<?php declare(strict_types=1);
namespace App\Reader;

use DOMNode;
use DOMXPath;
use DOMDocument;

trait XPath
{
    public function getXPath(DOMNode $node): DOMXPath
    {
        $document = new DOMDocument;
        $node = $document->importNode($node, true);
        $document->appendChild($node);

        return new DOMXPath($document);
    }
}
