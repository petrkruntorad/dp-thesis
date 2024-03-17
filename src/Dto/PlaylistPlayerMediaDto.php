<?php

namespace App\Dto;

class PlaylistPlayerMediaDto
{
    private ?int $id = null;
    private ?int $mediaId = null;
    private ?string $name = null;
    private ?\DateTime $showFrom = null;
    private ?float $showFromAsTimestamp = null;
    private ?\DateTime $showTo = null;
    private ?float $showToAsTimestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getMediaId(): ?int
    {
        return $this->mediaId;
    }

    public function setMediaId(?int $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
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

    public function getShowFromAsTimestamp(): ?float
    {
        return $this->showFromAsTimestamp;
    }

    public function setShowFromAsTimestamp(?float $showFromAsTimestamp): void
    {
        $this->showFromAsTimestamp = $showFromAsTimestamp;
    }

    public function getShowToAsTimestamp(): ?float
    {
        return $this->showToAsTimestamp;
    }

    public function setShowToAsTimestamp(?float $showToAsTimestamp): void
    {
        $this->showToAsTimestamp = $showToAsTimestamp;
    }


}
