<?php

namespace App\Services;

use App\Dto\PlayerConfigDto;
use App\Dto\PlaylistPlayerDto;
use App\Dto\PlaylistPlayerMediaDto;
use App\Entity\Device;
use App\Entity\PlaylistMedia;
use App\Repository\DeviceRepository;
use App\Repository\PlaylistMediaRepository;
use App\Repository\PlaylistRepository;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeviceService
{
    public function __construct(
        private readonly HashService $hashService,
        private readonly DeviceRepository $deviceRepository,
        private readonly PlaylistRepository $playlistRepository,
        private readonly PlaylistMediaRepository $playlistMediaRepository,
        private readonly SerializerInterface   $serializer,
        private readonly RouterInterface       $router
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function getUniqueHashForDevice(): string
    {
        try {
            // generates unique hash for device
            $hash = $this->hashService->generateHashWithLength();
            // checks if generated hash is unique
            $deviceWithGeneratedHash = $this->deviceRepository->findBy(['uniqueHash' => $hash]);
            // if hash is not unique, generates new hash
            while (count($deviceWithGeneratedHash) != 0)
            {
                $hash = $this->hashService->generateHashWithLength();
                $deviceWithGeneratedHash = $this->deviceRepository->findBy(['uniqueHash' => $hash]);
            }
            return $hash;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function generatePlaylistForDevice(Device $device)
    {
        $playlist = $device->getPlaylist();
        $now = new \DateTime();

        $playlistItems = $this->playlistMediaRepository->findBy(['playlist' => $playlist]);

        // inits playlist dto with basic data
        $playlistDto = new PlaylistPlayerDto();
        $playlistDto->setId($playlist->getId());
        $playlistDto->setName($playlist->getName());
        $playlistDto->setLastUpdated($playlist->getUpdatedAt());

        // gets currently played media for specific playlist
        $currentlyPlayedMedia = $this->playlistMediaRepository->getCurrentMediaForPlaylist($playlist);
        // gets following media for specific playlist
        $followingMediaData = $this->playlistMediaRepository->getFollowingMediaForPlaylist($playlist);

        // if there is no following media, check for following media for next day
        if(!$followingMediaData)
        {
            $followingMediaData = $this->playlistMediaRepository->getFollowingMediaForPlaylist($playlist, new \DateTime('tomorrow midnight'));
        }

        // checks if there is currently played media
        if($currentlyPlayedMedia)
        {
            //fixes date to current date since we only need time
            $currentMediaShowFrom = $currentlyPlayedMedia->getShowFrom();
            $currentMediaShowFrom->modify(date('Y-m-d'));

            //fixes date to current date since we only need time
            $currentMediaShowTo = $currentlyPlayedMedia->getShowTo();
            $currentMediaShowTo->modify(date('Y-m-d'));

            // inits current media dto with data for currently played media
            $currentMedia = new PlaylistPlayerMediaDto();
            $currentMedia->setId($currentlyPlayedMedia->getId());
            $currentMedia->setMediaId($currentlyPlayedMedia->getMedia()->getId());
            $currentMedia->setName($currentlyPlayedMedia->getMedia()->getName());
            $currentMedia->setShowFrom($currentMediaShowFrom);
            $currentMedia->setShowTo($currentMediaShowTo);
            $currentMedia->setShowFromAsTimestamp($currentMediaShowFrom->format('Uv'));
            $currentMedia->setShowToAsTimestamp($currentMediaShowTo->format('Uv'));
            $playlistDto->setCurrentMedia($currentMedia);
        }

        // checks if there is following media
        if($followingMediaData)
        {
            //fixes date to current date since we only need time
            $followingMediaShowFrom = $followingMediaData->getShowFrom();
            $followingMediaShowFrom->modify(date('Y-m-d'));

            //fixes date to current date since we only need time
            $followingMediaShowTo = $followingMediaData->getShowTo();
            $followingMediaShowTo->modify(date('Y-m-d'));

            // if following media show from is before current datetime, adjusts show from and show to for following media
            // this solves issue when there is not following media for today, but there is for tomorrow
            if(strtotime($followingMediaShowFrom->format('Y-m-d H:i:s')) < strtotime($now->format('Y-m-d H:i:s')))
            {
                $followingMediaShowFrom->modify('+1 day');
                $followingMediaShowTo->modify('+1 day');
            }

            // inits following media dto with data for following media
            $followingMedia = new PlaylistPlayerMediaDto();
            $followingMedia->setId($followingMediaData->getId());
            $followingMedia->setMediaId($followingMediaData->getMedia()->getId());
            $followingMedia->setName($followingMediaData->getMedia()->getName());
            $followingMedia->setShowFrom($followingMediaShowFrom);
            $followingMedia->setShowTo($followingMediaShowTo);
            $followingMedia->setShowFromAsTimestamp($followingMediaShowFrom->format('Uv'));
            $followingMedia->setShowToAsTimestamp($followingMediaShowTo->format('Uv'));
            $playlistDto->setFollowingMedia($followingMedia);

        }

        // converts playlist dto to json
        $data = $this->serializer->normalize($playlistDto);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function generateConfigForDevice(Device $device)
    {
        // inits player config dto with basic data
        $playerConfigDto = new PlayerConfigDto();
        $playerConfigDto->setUniqueHash($device->getUniqueHash());
        // sets urls for obtaining config, player and updating device details
        $playerConfigDto->setGetConfigUrl($this->router->generate('player_get_config', ['unique_hash'=>$device->getUniqueHash()], UrlGeneratorInterface::ABSOLUTE_URL));
        $playerConfigDto->setPlayerUrl($this->router->generate('player_index', ['unique_hash'=>$device->getUniqueHash()], UrlGeneratorInterface::ABSOLUTE_URL));
        $playerConfigDto->setUpdateDeviceDetailsUrl($this->router->generate('player_update_details', ['unique_hash'=>$device->getUniqueHash()], UrlGeneratorInterface::ABSOLUTE_URL));

        // converts player config dto to json
        $data = $this->serializer->normalize($playerConfigDto);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

}
