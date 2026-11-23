<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['pays:lire']],
    denormalizationContext: ['groups' => ['pays:ecrire']],
)]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pays:lire'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['pays:lire', 'pays:ecrire'])]
    private ?string $nom = null;


    #[ORM\Column(length: 3, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    #[Groups(['pays:lire'])]
    private ?string $codeAlpha3 = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['pays:lire', 'pays:ecrire'])]
    private ?int $population = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Groups(['pays:lire', 'pays:ecrire'])]
    private ?float $superficieKm2 = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['pays:lire', 'pays:ecrire'])]
    private ?string $capitale = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'pays')]
    #[Groups(['pays:lire'])]
    private Collection $paysFrontaliers;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'paysFrontaliers')]
    private Collection $pays;

    /**
     * @var Collection<int, Langue>
     */
    #[ORM\ManyToMany(targetEntity: Langue::class, inversedBy: 'pays')]
    #[Groups(['pays:lire'])]
    private Collection $langues;

    /**
     * @var Collection<int, Continent>
     */
    #[ORM\ManyToMany(targetEntity: Continent::class, inversedBy: 'pays')]
    #[Groups(['pays:lire'])]
    private Collection $continents;

    public function __construct()
    {
        $this->paysFrontaliers = new ArrayCollection();
        $this->pays = new ArrayCollection();
        $this->langues = new ArrayCollection();
        $this->continents = new ArrayCollection();
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

    public function getCodeAlpha3(): ?string
    {
        return $this->codeAlpha3;
    }

    public function setCodeAlpha3(string $codeAlpha3): static
    {
        $this->codeAlpha3 = $codeAlpha3;

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

    public function getSuperficieKm2(): ?float
    {
        return $this->superficieKm2;
    }

    public function setSuperficieKm2(?float $superficieKm2): static
    {
        $this->superficieKm2 = $superficieKm2;

        return $this;
    }

    public function getCapitale(): ?string
    {
        return $this->capitale;
    }

    public function setCapitale(?string $capitale): static
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

    /**
     * @return Collection<int, Langue>
     */
    public function getLangues(): Collection
    {
        return $this->langues;
    }

    public function addLangue(Langue $langue): static
    {
        if (!$this->langues->contains($langue)) {
            $this->langues->add($langue);
        }

        return $this;
    }

    public function removeLangue(Langue $langue): static
    {
        $this->langues->removeElement($langue);

        return $this;
    }

    /**
     * @return Collection<int, Continent>
     */
    public function getContinents(): Collection
    {
        return $this->continents;
    }

    public function addContinent(Continent $continent): static
    {
        if (!$this->continents->contains($continent)) {
            $this->continents->add($continent);
        }

        return $this;
    }

    public function removeContinent(Continent $continent): static
    {
        $this->continents->removeElement($continent);

        return $this;
    }
}