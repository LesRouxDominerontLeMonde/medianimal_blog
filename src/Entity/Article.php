<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Validator\RequiredMainImage;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[Vich\Uploadable]
#[RequiredMainImage]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isPublished = false;

    // Image principale (obligatoire)
    #[ORM\Column(length: 255)]
    private ?string $imageMainPath = null;

    /**
     * @var File|null
     */
    #[Vich\UploadableField(mapping: 'article_images', fileNameProperty: 'imageMainPath')]
    #[Assert\Image(
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Seuls les formats JPEG, PNG et WebP sont autorisés',
        maxSize: '2M',
        maxSizeMessage: 'Image trop lourde, max 2 Mo',
        minPixels: 50000,
        minPixelsMessage: 'L\'image doit contenir au minimum 50 000 pixels'
    )]
    private ?File $imageMainFile = null;

    // Image alternative (optionnelle)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAltPath = null;

    /**
     * @var File|null
     */
    #[Vich\UploadableField(mapping: 'article_images', fileNameProperty: 'imageAltPath')]
    #[Assert\Image(
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Seuls les formats JPEG, PNG et WebP sont autorisés',
        maxSize: '2M',
        maxSizeMessage: 'Image trop lourde, max 2 Mo',
        minPixels: 50000,
        minPixelsMessage: 'L\'image doit contenir au minimum 50 000 pixels'
    )]
    private ?File $imageAltFile = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isPublished = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Vérifie si l'article est visible publiquement
     * Un article est visible si :
     * - isPublished = true
     * - publishedAt est défini et <= maintenant
     */
    public function isVisible(): bool
    {
        return $this->isPublished 
            && $this->publishedAt !== null 
            && $this->publishedAt <= new \DateTimeImmutable();
    }

    /**
     * Vérifie si l'article a une image principale
     * (soit un fichier uploadé, soit un chemin existant)
     */
    public function hasMainImage(): bool
    {
        return $this->imageMainFile !== null || !empty($this->imageMainPath);
    }

    // Méthodes pour l'image principale
    public function getImageMainPath(): ?string
    {
        return $this->imageMainPath;
    }

    public function setImageMainPath(?string $imageMainPath): static
    {
        $this->imageMainPath = $imageMainPath;

        return $this;
    }

    public function setImageMainFile(?File $imageMainFile = null): void
    {
        $this->imageMainFile = $imageMainFile;

        if ($imageMainFile !== null) {
            // Pour forcer Doctrine à détecter un changement
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getImageMainFile(): ?File
    {
        return $this->imageMainFile;
    }

    // Méthodes pour l'image alternative
    public function getImageAltPath(): ?string
    {
        return $this->imageAltPath;
    }

    public function setImageAltPath(?string $imageAltPath): static
    {
        $this->imageAltPath = $imageAltPath;

        return $this;
    }

    public function setImageAltFile(?File $imageAltFile = null): void
    {
        $this->imageAltFile = $imageAltFile;

        if ($imageAltFile !== null) {
            // Pour forcer Doctrine à détecter un changement
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getImageAltFile(): ?File
    {
        return $this->imageAltFile;
    }
}
