<?php

namespace App\Dto;

class PlaylistPlayerDto
{
    private ?int $id = null;
    private ?string $name = null;
    private ?PlaylistPlayerMediaDto $currentMedia = null;
    private ?PlaylistPlayerMediaDto $followingMedia = null;
    private ?\DateTime $lastUpdated = null;

    /**
     * @var PlaylistPlayerMediaDto[] $media
     */
    private array $media = array();

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCurrentMedia(): ?PlaylistPlayerMediaDto
    {
        return $this->currentMedia;
    }

    public function setCurrentMedia(?PlaylistPlayerMediaDto $currentMedia): void
    {
        $this->currentMedia = $currentMedia;
    }

    public function getMedia(): array
    {
        return $this->media;
    }

    public function setMedia(array $media): void
    {
        $this->media = $media;
    }

    public function addMedia(PlaylistPlayerMediaDto $media): void
    {
        $this->media[] = $media;
    }

    public function getFollowingMedia(): ?PlaylistPlayerMediaDto
    {
        return $this->followingMedia;
    }

    public function setFollowingMedia(?PlaylistPlayerMediaDto $followingMedia): void
    {
        $this->followingMedia = $followingMedia;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?\DateTime $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }
}
