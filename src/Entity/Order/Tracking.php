<?php declare(strict_types=1);
namespace App\Entity\Order;

use App\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class Tracking
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"tracking:read", "tracking:write"})
     */
    private $number;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"tracking:read", "tracking:write"})
     */
    private $method;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"tracking:read", "tracking:write"})
     */
    private $url;

    /**
     * @var Order
     * @ORM\OneToOne(targetEntity=Order::class)
     * @Serializer\Groups({"tracking:read", "tracking:write"})
     */
    private $order;

    public function __construct(Order $order, string $method, string $number, string $url)
    {
        $this->order = $order;
        $this->method = $method;
        $this->number = $number;
        $this->url = $url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
