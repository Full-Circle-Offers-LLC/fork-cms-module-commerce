<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\ProductDimension\ProductDimension;
use Backend\Modules\Commerce\Domain\ProductDimensionNotification\ProductDimensionNotification;
use Backend\Modules\Commerce\Domain\ProductSpecial\ProductSpecial;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueRepository;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\UpSellProduct\UpSellProduct;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type as MediaGroupType;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Type;
use Common\Doctrine\Entity\Meta;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDataTransferObject
{
    /**
     * @var Product
     */
    protected $productEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var boolean
     */
    public $hidden = false;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $type = Product::TYPE_DEFAULT;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $min_width = 0;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $min_height = 0;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $max_width = 0;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $max_height = 0;

    /**
     * @var int
     */
    public $extra_production_width = 0;

    /**
     * @var int
     */
    public $extra_production_height = 0;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $summary;

    /**
     * @var string
     */
    public $weight;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $price;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $stock;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $order_quantity = 1;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $sku;

    /**
     * @var string
     */
    public $ean13;

    /**
     * @var string
     */
    public $isbn;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $dimension_instructions;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var Category
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $category;

    /**
     * @var Brand
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $brand;

    /**
     * @var Vat
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $vat;

    /**
     * @var StockStatus
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $stock_status;

    /**
     * @var boolean
     */
    public $from_stock = true;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var Image
     */
    public $image;

    /**
     * @var PersistentCollection
     */
    public $specification_values;

    /**
     * @Assert\Valid(groups={"dimensions"})
     *
     * @var PersistentCollection
     */
    public $specials;

    /**
     * @var PersistentCollection
     */
    public $remove_specials;

    /**
     * @Assert\Valid
     *
     * @var PersistentCollection
     */
    public $dimensions;

    /**
     * @var PersistentCollection
     */
    public $remove_dimensions;

    /**
     * @Assert\Valid
     *
     * @var PersistentCollection
     */
    public $dimension_notifications;

    /**
     * @var PersistentCollection
     */
    public $remove_dimension_notifications;

    /**
     * @var PersistentCollection
     */
    public $related_products;

    /**
     * @var PersistentCollection
     */
    public $up_sell_products;

    /**
     * @var PersistentCollection
     */
    public $remove_up_sell_products;

    /**
     * @var PersistentCollection
     */
    public $remove_specification_values;

    /**
     * @var PersistentCollection
     */
    public $remove_related_products;

    /**
     * @var MediaGroup
     */
    public $images;

    /**
     * @var MediaGroup
     */
    public $downloads;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\GreaterThanOrEqual(value=1, message="err.SequenceInvalid")
     */
    public $sequence;

    public function __construct(Product $product = null)
    {
        // Set some default values
        $this->locale = Locale::workingLocale();
        $this->productEntity = $product;
        $this->specification_values = new ArrayCollection();
        $this->remove_specification_values = new ArrayCollection();
        $this->specials = new ArrayCollection();
        $this->remove_specials = new ArrayCollection();
        $this->dimensions = new ArrayCollection();
        $this->remove_dimensions = new ArrayCollection();
        $this->dimension_notifications = new ArrayCollection();
        $this->remove_dimension_notifications = new ArrayCollection();
        $this->related_products = new ArrayCollection();
        $this->up_sell_products = new ArrayCollection();
        $this->remove_up_sell_products = new ArrayCollection();
        $this->remove_related_products = new ArrayCollection();
        $this->images = MediaGroup::create(MediaGroupType::fromString(Type::IMAGE));
        $this->downloads = MediaGroup::create(MediaGroupType::fromString(Type::FILE));
        $this->weight = (float)0.00;
        $this->sequence = $this->getProductRepository()->getNextSequence($this->locale, $this->category);

        if (!$this->hasExistingProduct()) {
            return;
        }

        $this->id = $product->getId();
        $this->meta = $product->getMeta();
        $this->category = $product->getCategory();
        $this->brand = $product->getBrand();
        $this->vat = $product->getVat();
        $this->stock_status = $product->getStockStatus();
        $this->hidden = $product->isHidden();
        $this->type = $product->getType();
        $this->min_width = $product->getMinWidth();
        $this->min_height = $product->getMinHeight();
        $this->max_width = $product->getMaxWidth();
        $this->max_height = $product->getMaxHeight();
        $this->extra_production_width = $product->getExtraProductionWidth();
        $this->extra_production_height = $product->getExtraProductionHeight();
        $this->title = $product->getTitle();
        $this->summary = $product->getSummary();
        $this->text = $product->getText();
        $this->dimension_instructions = $product->getDimensionInstructions();
        $this->locale = $product->getLocale();
        $this->weight = $product->getWeight();
        $this->price = $product->getPrice();
        $this->stock = $product->getStock();
        $this->order_quantity = $product->getOrderQuantity();
        $this->from_stock = $product->isFromStock();
        $this->sku = $product->getSku();
        $this->ean13 = $product->getEan13();
        $this->isbn = $product->getIsbn();
        $this->sequence = $product->getSequence();
        $this->specification_values = $product->getSpecificationValues();
        $this->specials = $product->getSpecials();
        $this->dimensions = $product->getDimensions();
        $this->dimension_notifications = $product->getDimensionNotifications();
        $this->images = $product->getImages();
        $this->downloads = $product->getDownloads();
        $this->related_products = $product->getRelatedProducts();
        $this->up_sell_products = $product->getUpSellProducts();

        // just a fallback
        if (!$this->images instanceof MediaGroup) {
            $this->images = MediaGroup::create(MediaGroupType::fromString(Type::IMAGE));
        }

        // just a fallback
        if (!$this->downloads instanceof MediaGroup) {
            $this->downloads = MediaGroup::create(MediaGroupType::fromString(Type::FILE));
        }
    }

    public function setProductEntity(Product $productEntity): void
    {
        $this->productEntity = $productEntity;
    }

    public function getProductEntity(): Product
    {
        return $this->productEntity;
    }

    public function hasExistingProduct(): bool
    {
        return $this->productEntity instanceof Product;
    }

    public function copy()
    {
        $this->id = null;
        $this->productEntity = null;
    }

    public function addSpecificationValue(SpecificationValue $value)
    {
        // If the specification value has no entity save a new one
        if (!$value->getId()) {
            /**
             * @var SpecificationValueRepository $specificationValueRepository
             */
            $specificationValueRepository = Model::get('commerce.repository.specification_value');

            $value->setSequence($specificationValueRepository->getNextSequence($value));
        }

        $this->specification_values->add($value);
    }

    public function removeSpecificationValue($value)
    {
        $this->specification_values->remove($value->getId());
        $this->remove_specification_values->add($value);
    }

    public function addRelatedProduct(Product $product)
    {
        $this->related_products->add($product);
    }

    public function removeRelatedProduct(Product $product)
    {
        // for our current entity
        $this->related_products->removeElement($product);

        // for our update to remove this entity
        $this->remove_related_products->add($product);
    }

    public function addUpSellProduct(UpSellProduct $upSellProduct)
    {
        $this->up_sell_products->add($upSellProduct);
    }

    public function removeUpSellProduct(UpSellProduct $upSellProduct)
    {
        // for our current entity
        $this->up_sell_products->removeElement($upSellProduct);

        $this->remove_up_sell_products->add($upSellProduct);
    }

    public function addSpecial(ProductSpecial $special)
    {
        $this->specials->add($special);
    }

    public function removeSpecial(ProductSpecial $special)
    {
        // for our current entity
        $this->remove_specials->add($special);
    }

    public function addDimension(ProductDimension $dimension)
    {
        if ($this->type != Product::TYPE_DIMENSIONS) {
            return;
        }

        $this->dimensions->add($dimension);
    }

    public function removeDimension(ProductDimension $dimension)
    {
        $this->remove_dimensions->add($dimension);
    }

    public function addDimensionNotification(ProductDimensionNotification $dimensionNotification)
    {
        $this->dimension_notifications->add($dimensionNotification);
    }

    public function removeDimensionNotification(ProductDimensionNotification $dimensionNotification)
    {
        $this->remove_dimension_notifications->add($dimensionNotification);
    }

    private function getProductRepository(): ProductRepository
    {
        return Model::get('commerce.repository.product');
    }
}
