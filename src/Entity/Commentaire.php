<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Professionnel::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Professionnel $professionnel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'Veuillez saisir un email valide')]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Regex(pattern: '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/', message: 'Format de téléphone invalide')]
    private ?string $telephone = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: 'La note est obligatoire')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être comprise entre 1 et 5')]
    private ?int $rating = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le commentaire est obligatoire')]
    #[Assert\Length(min: 10, max: 1000, minMessage: 'Le commentaire doit faire au moins 10 caractères', maxMessage: 'Le commentaire ne peut pas dépasser 1000 caractères')]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVisible = true;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfessionnel(): ?Professionnel
    {
        return $this->professionnel;
    }

    public function setProfessionnel(?Professionnel $professionnel): static
    {
        $this->professionnel = $professionnel;
        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     * Retourne un array d'étoiles pour l'affichage
     */
    public function getStarsArray(): array
    {
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[] = $i <= $this->rating;
        }
        return $stars;
    }

    /**
     * Formate la date de création
     */
    public function getDateCreationFormatted(): string
    {
        return $this->dateCreation->format('d/m/Y à H:i');
    }
} 