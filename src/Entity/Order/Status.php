<?php declare(strict_types=1);
namespace App\Entity\Order;

use App\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class Status
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
     * @Serializer\Groups({"status:read", "status:write"})
     */
    private $source;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"status:read", "status:write"})
     */
    private $status;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity=Order::class)
     * @Serializer\Groups({"status:read", "status:write"})
     */
    private $order;

    public function __construct(Order $order, string $source)
    {
        $this->order = $order;
        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
