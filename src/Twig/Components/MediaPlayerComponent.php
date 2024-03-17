<?php

namespace App\Twig\Components;

use App\Entity\Device;
use App\Entity\Media;
use App\Entity\PlaylistMedia;
use App\Repository\PlaylistMediaRepository;
use App\Services\DeviceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class MediaPlayerComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?Device $device = null;

    #[LiveProp(writable: true)]
    public ?PlaylistMedia $currentPlaylistMedia = null;

    #[LiveProp(writable: true)]
    public ?Media $currentMedia = null;

    public function __construct(
        private readonly PlaylistMediaRepository $playlistMediaRepository,
        private readonly DeviceService $deviceService
    )
    {
    }

    public function mount(Device $device): void
    {
        $this->device = $device;
        // sets current media and playlist media
        $this->currentPlaylistMedia = $device->getCurrentlyPlayedMedia();
        $this->currentMedia = $device->getCurrentlyPlayedMedia()?->getMedia();
    }

    #[LiveListener('media:update')]
    public function refreshMedia(): void
    {
        // checks if device has playlist
        if($this->device->getPlaylist())
        {
            // gets current media for playlist from database
            $currentPlaylistMedia = $this->playlistMediaRepository->getCurrentMediaForPlaylist($this->device->getPlaylist());
            // if current media is set
            if($currentPlaylistMedia)
            {
                // sets current media and playlist media
                $this->currentPlaylistMedia = $currentPlaylistMedia;
                $this->currentMedia = $currentPlaylistMedia->getMedia();
            }

        }
    }
}
