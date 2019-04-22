<?php declare(strict_types=1);
namespace App\Entity;

use App\Entity\Order\Status;
use App\Entity\Order\Tracking;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation as Api;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table
 *
 * @Api\ApiResource(
 *     normalizationContext={"groups":{"order:read", "tracking:read", "status:read"}},
 *     denormalizationContext={"groups":{"order:write", "tracking:write", "status:write"}}
 * )
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"order:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Serializer\Groups({"order:read", "order:write"})
     */
    private $marketplace;

    /**
     * @var ?string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"order:read", "order:write"})
     */
    private $billingAddress;

    /**
     * @var Collection&iterable<Status>
     *
     * @ORM\OnetoMany(targetEntity=Status::class, cascade={"all"})
     * @Serializer\Groups({"order:read", "order:write"})
     */
    private $statuses;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"order:read", "order:write"})
     */
    private $deliveryAddress;

    /**
     * @var ?Tracking
     * @ORM\OnetoMany(targetEntity=Tracking::class, cascade={"all"})
     * @Serializer\Groups({"order:read", "order:write"})
     */
    private $tracking;

    public function __construct(string $marketplace, string $deliveryAddress)
    {
        $this->marketplace = $marketplace;
        $this->deliveryAddress = $deliveryAddress;

        $this->statuses = new ArrayCollection;
    }

    public function getMarketplace(): string
    {
        return $this->marketplace;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?string $address): void
    {
        $this->billingAddress = $address;
    }

    public function getDeliveryAddress(): string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(string $address): void
    {
        $this->deliveryAddress = $address;
    }

    public function getTracking(): ?Tracking
    {
        return $this->tracking;
    }

    public function setTracking(?Tracking $tracking): void
    {
        $this->tracking = $tracking;
    }

    /** @return Collection&iterable<Status> */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(Status $status): void
    {
        $this->statuses[] = $status;
    }
}
