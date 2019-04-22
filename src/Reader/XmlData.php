<?php declare(strict_types=1);
namespace App\Reader;

use DOMNode;

/**
 * Value-Object representing an extracted data from source file
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class XmlData
{
    protected $node;

    public function __construct(DOMNode $node)
    {
        $this->node = $node;
    }

    /** @return DOMNode */
    public function getDom(): DOMNode
    {
        return $this->node;
    }
}
