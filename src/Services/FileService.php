<?php

namespace App\Services;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly MediaRepository       $mediaRepository
    )
    {
    }

    public function uploadMultimedia(UploadedFile $file): string
    {
        $basePath = $this->parameterBag->get('uploads_multimedia_directory');

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move the file to the directory where brochures are stored
        $file->move(
            $basePath,
            $newFilename
        );

        return $newFilename;
    }

    public function deleteMultimediaFile(Media $media): void
    {
        $fileUsage = $this->mediaRepository->findBy(['mediaData' => $media->getMediaData()]);

        //checks if multimedia file is used to prevent removal of used multimedia
        if (count($fileUsage) > 1) {
            return;
        }

        //generates full path to multimedia file
        $basePath = $this->parameterBag->get('uploads_multimedia_directory');
        $file = $basePath . '/' . $media->getMediaData();

        //removes multimedia file
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
