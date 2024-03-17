<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[UniqueEntity(fields: ['name', 'uniqueHash'])]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $uniqueHash = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localIpAddress = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'devices')]
    private ?Playlist $playlist = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $diskUsage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $diskCapacity = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $firstConnection = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastConnection = null;



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

    public function getUniqueHash(): ?string
    {
        return $this->uniqueHash;
    }

    public function setUniqueHash(string $uniqueHash): static
    {
        $this->uniqueHash = $uniqueHash;

        return $this;
    }

    public function getLocalIpAddress(): ?string
    {
        return $this->localIpAddress;
    }

    public function setLocalIpAddress(string $localIpAddress): static
    {
        $this->localIpAddress = $localIpAddress;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
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

    //custom methods
    public function getCurrentlyPlayedMedia()
    {
        $playlist = $this->getPlaylist();
        $currentMedia = null;
        if(!$playlist)
        {
            return $currentMedia;
        }

        foreach($playlist->getPlaylistMedia() as $playlistItem)
        {
            $now = new \DateTime();
            $nowAsFormattedString = $now->format('H:i:s');
            $mediaShowFromAsFormattedString = $playlistItem->getShowFrom()->format('H:i:s');
            $mediaShowToAsFormattedString = $playlistItem->getShowTo()->format('H:i:s');
            if($mediaShowFromAsFormattedString <= $nowAsFormattedString && $mediaShowToAsFormattedString >= $nowAsFormattedString)
            {
                $currentMedia = $playlistItem;
                break;
            }
        }

        return $currentMedia;
    }

    public function getDiskUsage(): ?string
    {
        return $this->diskUsage;
    }

    public function setDiskUsage(?string $diskUsage): static
    {
        $this->diskUsage = $diskUsage;

        return $this;
    }

    public function getDiskCapacity(): ?string
    {
        return $this->diskCapacity;
    }

    public function setDiskCapacity(?string $diskCapacity): static
    {
        $this->diskCapacity = $diskCapacity;

        return $this;
    }

    public function getFirstConnection(): ?\DateTimeInterface
    {
        return $this->firstConnection;
    }

    public function setFirstConnection(?\DateTimeInterface $firstConnection): static
    {
        $this->firstConnection = $firstConnection;

        return $this;
    }

    public function getLastConnection(): ?\DateTimeInterface
    {
        return $this->lastConnection;
    }

    public function setLastConnection(?\DateTimeInterface $lastConnection): static
    {
        $this->lastConnection = $lastConnection;

        return $this;
    }
}
