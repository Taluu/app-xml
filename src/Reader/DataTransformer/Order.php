<?php declare(strict_types=1);
namespace App\Reader\DataTransformer;

use DOMNodeList;
use DOMXPath;
use DOMCdataSection;

use InvalidArgumentException;

use App\Reader\XPath;
use App\Reader\XmlData;
use App\Entity;

final class Order
{
    use XPath;

    public function transform(XmlData $data): Entity\Order
    {
        $xpath = $this->getXPath($data->getDOM());

        $order = new Entity\Order(
            $this->getText($xpath->evaluate('/marketplace/text()')),
            $this->getText($xpath->evaluate('/delivery_address/delivery_full_address/text()'))
        );

        try {
            $order->setBillingAddress($this->getText($xpath->evaluate('/billing_address/billing_full_address/text()')));
        } catch (InvalidArgumentException $e) {
            // no node found, we don't care
            // note : transform this into a proper exception
        }

        try {
            $tracking = new Entity\Order\Tracking(
                $order,
                $this->getText($xpath->evaluate('/tracking_informations/tracking_method/text()')),
                $this->getText($xpath->evaluate('/tracking_informations/tracking_number/text()')),
                $this->getText($xpath->evaluate('/tracking_informations/tracking_url/text()'))
            );

            $order->setTracking($tracking);
        } catch (InvalidArgumentException $e) {
            // no node found, we don't care
            // note : transform this into a proper exception
        }

        foreach ($this->buildStatuses($xpath, $order) as $status) {
            $order->addStatus($status);
        }

        return $order;
    }

    private function buildStatuses(DOMXPath $xpath, Entity\Order $order): iterable
    {
        $eval = $xpath->evaluate('/order_status/*/text()');

        foreach ($eval as $node) {
            $status = new Entity\Order\Status($order, $node->parentNode->localName);
            $status->setStatus($node->wholeText);

            yield $status;
        }
    }

    private function getText(DOMNodeList $list): string
    {
        if ($list->length !== 1 || !$list[0] instanceof DOMCdataSection) {
            throw new InvalidArgumentException('Node notf ound, aborting...');
        }

        return $list[0]->wholeText;
    }
}
