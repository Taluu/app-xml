<?php declare(strict_types=1);
namespace App\Reader;

use Iterator;
use Generator;
use Traversable;
use RuntimeException;
use InvalidArgumentException;

use XMLReader;

use DOMNode;
use DOMText;
use DOMXPath;

/**
 * Read data from a XML file
 *
 * Uses XMLReader (a built-in incremental xml puller), because the xml file can
 * be huuuge, and using DOM or SimpleXML on that may make the perfs go berserk
 *
 * Uses a bit of DOM and XPath too, for the complex elements (see notes on
 * read() method)
 *
 * @link http://nl1.php.net/manual/en/book.xmlreader.php
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class DataReader
{
    use XPath;

    /** @var XMLReader */
    private $reader;

    public function __construct()
    {
        $this->reader = new XMLReader;
    }

    /**
     * Simple xml reader with XMLReader ; if we encounter a Generator, we may
     * yield from it, and go the next sibling (if there is one). Otherwise, we
     * need to go DEEEEEPAAAAR.
     *
     * @see http://php.net/manual/en/xmlreader.read.php
     * @see http://php.net/manual/en/xmlreader.next.php
     */
    public function read(string $file): Iterator
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid file', $file));
        }

        if (!is_readable($file)) {
            throw new RuntimeException(sprintf('%s is not readable', $file));
        }

        if ('application/xml' !== $type = mime_content_type($file)) {
            throw new InvalidArgumentException(sprintf('This application supports only xml file, %s given', $type));
        }

        if (false === $this->reader->open($file)) {
            throw new RuntimeException(sprintf('An error occurred while trying to read from %s', $file));
        }

        try {
            $this->reader->read();

            do {
                $data = yield from $this->readNode();

                if ($data instanceof XmlData) {
                    yield $data;

                    $this->reader->next();
                    continue;
                }

                $this->reader->read();
            } while (XMLReader::NONE !== $this->reader->nodeType);
        } finally {
            $this->reader->close();
        }
    }

    /**
     * Read the current node.
     *
     * If it is not a ELEMENT, we don't really care so we can get to the next
     * node (with XMLReader::read()).
     *
     * If it is an ELEMENT, we can process it if it is an ELEMENT that can be of
     * interest (namely, an Order)
     */
    private function readNode(): Generator
    {
        if (XMLReader::ELEMENT !== $this->reader->nodeType) {
            return;
        }

        // an empty element is of no interest to us
        if ($this->reader->isEmptyElement) {
            return;
        }

        if ($this->reader->name !== 'order') {
            return;
        }

        yield new XmlData($this->reader->expand());
    }
}
