<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ApiResource()
 * @ApiFilter(SearchFilter::class, properties={
 *  "id": "exact", "taxonomy": "exact",
 *  "name": "partial", "description": "partial"
 * })
 * @ApiFilter(RangeFilter::class, properties={"price"})
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @Vich\Uploadable
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $priceTax;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, options={"default":21})
     */
    private $tax = 21;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Taxonomy", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $taxonomy;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     */
    public $image;

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;
        $this->setPriceTax($price);

        return $this;
    }

    public function getPriceTax()
    {
        return $this->priceTax;
    }

    public function setPriceTax($priceTax): self
    {
        $this->priceTax = 0;

        if ($priceTax > 0 && $this->tax > 0) {
            $this->priceTax = $priceTax + ($priceTax / 100 * $this->tax);
        }

        return $this;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function setTax($tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTaxonomy(): ?Taxonomy
    {
        return $this->taxonomy;
    }

    public function setTaxonomy(?Taxonomy $taxonomy): self
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }
}
