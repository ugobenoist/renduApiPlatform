<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[ApiResource]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique:true)]
    private ?string $nom = null;

    #[ORM\Column(length: 3,unique:true)]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    private ?int $population = null;

    #[ORM\Column]
    private ?int $superficie = null;

    #[ORM\Column(length: 150)]
    private ?string $capitale = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'pays')]
    private Collection $paysFrontaliers;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'paysFrontaliers')]
    private Collection $pays;

    public function __construct()
    {
        $this->paysFrontaliers = new ArrayCollection();
        $this->pays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): static
    {
        $this->population = $population;

        return $this;
    }

    public function getSuperficie(): ?int
    {
        return $this->superficie;
    }

    public function setSuperficie(int $superficie): static
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getCapitale(): ?string
    {
        return $this->capitale;
    }

    public function setCapitale(string $capitale): static
    {
        $this->capitale = $capitale;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPaysFrontaliers(): Collection
    {
        return $this->paysFrontaliers;
    }

    public function addPaysFrontalier(self $paysFrontalier): static
    {
        if (!$this->paysFrontaliers->contains($paysFrontalier)) {
            $this->paysFrontaliers->add($paysFrontalier);
        }

        return $this;
    }

    public function removePaysFrontalier(self $paysFrontalier): static
    {
        $this->paysFrontaliers->removeElement($paysFrontalier);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPays(): Collection
    {
        return $this->pays;
    }

    public function addPay(self $pay): static
    {
        if (!$this->pays->contains($pay)) {
            $this->pays->add($pay);
            $pay->addPaysFrontalier($this);
        }

        return $this;
    }

    public function removePay(self $pay): static
    {
        if ($this->pays->removeElement($pay)) {
            $pay->removePaysFrontalier($this);
        }

        return $this;
    }
}
