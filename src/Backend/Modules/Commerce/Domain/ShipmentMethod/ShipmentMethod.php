<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_shipment_methods")
 * @ORM\Entity(repositoryClass="ShipmentMethodRepository")
 */
class ShipmentMethod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $module;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $isEnabled;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

    public function __construct(
        string $name,
        string $module,
        bool $isEnabled,
        Locale $locale
    ) {
        $this->name = $name;
        $this->module = $module;
        $this->isEnabled = $isEnabled;
        $this->locale = $locale;
    }

    public static function fromDataTransferObject(ShipmentMethodDataTransferObject $dataTransferObject): ShipmentMethod
    {
        if ($dataTransferObject->hasExistingShipmentMethod()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(ShipmentMethodDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->name,
            $dataTransferObject->module,
            $dataTransferObject->isEnabled,
            $dataTransferObject->locale
        );
    }

    private static function update(ShipmentMethodDataTransferObject $dataTransferObject): self
    {
        $paymentMethod = $dataTransferObject->getShipmentMethod();

        $paymentMethod->name = $dataTransferObject->name;
        $paymentMethod->module = $dataTransferObject->module;
        $paymentMethod->isEnabled = $dataTransferObject->isEnabled;
        $paymentMethod->locale = $dataTransferObject->locale;

        return $paymentMethod;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
