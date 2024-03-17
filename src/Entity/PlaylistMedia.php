<?php

namespace App\Entity;

use App\Repository\PlaylistMediaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistMediaRepository::class)]
class PlaylistMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'playlistMedia')]
    private ?Playlist $playlist = null;

    #[ORM\ManyToOne(inversedBy: 'playlistMedia')]
    private ?Media $media = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $showFrom = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $showTo = null;

    #[ORM\Column]
    private ?bool $customTime = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaylist(): ?Playlist
    {
        return $this->playlist;
    }

    public function setPlaylist(?Playlist $playlist): static
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getShowFrom(): ?\DateTime
    {
        return $this->showFrom;
    }

    public function setShowFrom(?\DateTime $showFrom): void
    {
        $this->showFrom = $showFrom;
    }

    public function getShowTo(): ?\DateTime
    {
        return $this->showTo;
    }

    public function setShowTo(?\DateTime $showTo): void
    {
        $this->showTo = $showTo;
    }

    public function isCustomTime(): ?bool
    {
        return $this->customTime;
    }

    public function setCustomTime(bool $customTime): static
    {
        $this->customTime = $customTime;

        return $this;
    }

    public function getShowFromAsTimestamp(): ?float
    {
        return ($this->showFrom)->format('Uv');
    }

    public function getShowToAsTimestamp(): ?float
    {
        return ($this->showTo)->format('Uv');
    }
}
