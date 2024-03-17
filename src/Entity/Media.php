<?php

namespace App\Entity;

use App\Enum\MediaTypeEnum;
use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[UniqueEntity('name')]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mediaData = null;

    #[ORM\Column(type: Types::STRING, enumType: MediaTypeEnum::class)]
    private ?MediaTypeEnum $mediaType = MediaTypeEnum::IMAGE;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: PlaylistMedia::class)]
    private Collection $playlistMedia;

    public function __construct()
    {
        $this->playlistMedia = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMediaData(): ?string
    {
        return $this->mediaData;
    }

    public function setMediaData(string $mediaData): static
    {
        $this->mediaData = $mediaData;

        return $this;
    }

    public function getMediaType(): ?MediaTypeEnum
    {
        return $this->mediaType;
    }

    public function setMediaType(?MediaTypeEnum $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    /**
     * @return Collection<int, PlaylistMedia>
     */
    public function getPlaylistMedia(): Collection
    {
        return $this->playlistMedia;
    }

    public function addPlaylistMedium(PlaylistMedia $playlistMedium): static
    {
        if (!$this->playlistMedia->contains($playlistMedium)) {
            $this->playlistMedia->add($playlistMedium);
            $playlistMedium->setMedia($this);
        }

        return $this;
    }

    public function removePlaylistMedium(PlaylistMedia $playlistMedium): static
    {
        if ($this->playlistMedia->removeElement($playlistMedium)) {
            // set the owning side to null (unless already changed)
            if ($playlistMedium->getMedia() === $this) {
                $playlistMedium->setMedia(null);
            }
        }

        return $this;
    }
}
