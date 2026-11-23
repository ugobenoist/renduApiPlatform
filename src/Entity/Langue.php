<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LangueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LangueRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['langue:lire']],
    denormalizationContext: ['groups' => ['langue:ecrire']],
)]
class Langue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['langue:lire'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['langue:lire', 'langue:ecrire', 'pays:lire'])]
    private ?string $nom = null;

    // lecture seule : clé naturelle (ISO 639-3)
    #[ORM\Column(length: 3, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    #[Groups(['langue:lire'])]
    private ?string $codeIso = null;

    /**
     * @var Collection<int, Pays>
     */
    #[ORM\ManyToMany(targetEntity: Pays::class, mappedBy: 'langues')]
    private Collection $pays;

    public function __construct()
    {
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

    public function getCodeIso(): ?string
    {
        return $this->codeIso;
    }

    public function setCodeIso(string $codeIso): static
    {
        $this->codeIso = $codeIso;

        return $this;
    }

    /**
     * @return Collection<int, Pays>
     */
    public function getPays(): Collection
    {
        return $this->pays;
    }

    public function addPay(Pays $pay): static
    {
        if (!$this->pays->contains($pay)) {
            $this->pays->add($pay);
            $pay->addLangue($this);
        }

        return $this;
    }

    public function removePay(Pays $pay): static
    {
        if ($this->pays->removeElement($pay)) {
            $pay->removeLangue($this);
        }

        return $this;
    }
}