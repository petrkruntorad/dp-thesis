<?php

namespace App\Controller\admin;

use App\Entity\Media;
use App\Entity\Playlist;
use App\Enum\MediaTypeEnum;
use App\Form\admin\MediaFormType;
use App\Form\admin\PlaylistFormType;
use App\Repository\MediaRepository;
use App\Repository\PlaylistMediaRepository;
use App\Repository\PlaylistRepository;
use App\Services\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/media', name: 'admin_media_')]
class AdminMediaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MediaRepository $mediaRepository,
        private readonly PaginatorInterface $paginator,
        private readonly FileService $fileService,
        private readonly PlaylistMediaRepository $playlistMediaRepository,
    )
    {
    }
    #[Route('/', name: 'index')]
    public function index(Request $request)
    {
        $media = $this->mediaRepository->getMediaAsQuery();
        $paginator = $this->paginator->paginate($media, $request->query->getInt('page', 1), 20, ['distinct' => false]);
        return $this->render('admin/media/index.html.twig', [
            'media' => $paginator,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        //form init
        $media = new Media();
        $form = $this->createForm(MediaFormType::class, $media);
        $form->handleRequest($request);

        //if form is submitted and is valid by values on the backend
        if($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $media = $form->getData();
                if($media instanceof Media) {
                    //checks if media type is image and then uploads it
                    if($media->getMediaType() === MediaTypeEnum::IMAGE || $media->getMediaType() === MediaTypeEnum::VIDEO) {
                        /** @var UploadedFile $multimedia */
                        $multimediaFile = $form?->get('mediaData')->getData();
                        if ($multimediaFile) {
                            $newFilename = $this->fileService->uploadMultimedia($multimediaFile);
                            $media->setMediaData($newFilename);
                        }
                    }

                    //saves media to database
                    $this->entityManager->persist($media);
                    $this->entityManager->flush();

                    //returns success message
                    $this->addFlash(
                        'success',
                        'Multimédium s názvem: '.$media->getName().' bylo úspěšně přidáno.'
                    );
                }else{
                    //returns error message
                    $this->addFlash(
                        'success',
                        'Něco se pokazilo, zkuste to prosím znovu.'
                    );
                }

                //redirects to media overview
                return $this->redirectToRoute('admin_media_index');
            }
            catch (Exception $exception)
            {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: '.$exception->getMessage()
                );
            }
        }

        return $this->render('admin/media/create.html.twig', [
            'media_form' => $form,
            'media' => $media,
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(Media $media)
    {
        return $this->render('admin/media/detail.html.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(Request $request, Media $media)
    {
        //form init
        $form = $this->createForm(MediaFormType::class, $media, ['is_edit' => true]);

        //clones media before submit
        $originalFile = clone $media;

        //handles request
        $form->handleRequest($request);
        //if form is submitted and is valid by values on the backend
        if($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $media = $form->getData();

                //checks if media is instance of entity Media
                if($media instanceof Media) {
                    //checks if media type is image and then uploads it
                    if($media->getMediaType() === MediaTypeEnum::IMAGE || $media->getMediaType() === MediaTypeEnum::VIDEO) {
                        /** @var UploadedFile $multimedia */
                        $multimediaFile = $form?->get('mediaData')->getData();
                        if ($multimediaFile) {
                            $newFilename = $this->fileService->uploadMultimedia($multimediaFile);
                            $media->setMediaData($newFilename);
                        }
                    }

                    // checks if media data has changed
                    if($originalFile->getMediaData() !== $media->getMediaData())
                    {
                        //iterates through all playlists and sets updated at to current time
                        foreach ($media->getPlaylistMedia() as $playlistMedia) {
                            $playlist =$playlistMedia->getPlaylist();
                            //sets updated at to current time
                            $playlist->setUpdatedAt(new \DateTime());
                            $this->entityManager->persist($playlist);
                        }
                    }

                    //saves changes to db
                    $this->entityManager->persist($media);
                    $this->entityManager->flush();

                    //returns success message
                    $this->addFlash(
                        'success',
                        'Multimédium bylo úspěšně upraveno.'
                    );
                }else{
                    //returns error message
                    $this->addFlash(
                        'success',
                        'Něco se pokazilo, zkuste to prosím znovu.'
                    );
                }

                //redirects to media overview
                return $this->redirectToRoute('admin_media_index');
            }
            catch (Exception $exception)
            {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: '.$exception->getMessage()
                );
            }
        }

        return $this->render('admin/media/update.html.twig', [
            'media_form' => $form->createView(),
            'media' => $media,
        ]);
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Media $media)
    {
        try {
            // finds related playlist
            $relatedPlaylistMedia = $this->playlistMediaRepository->findBy(['media' => $media]);
            // checks if media is related to any playlist
            if(count($relatedPlaylistMedia) > 0)
            {
                //returns error message
                $this->addFlash(
                    'error',
                    'Multimédium se nepodařilo smazat, protože je přiřazeno k seznamu přehrání.'
                );
                return $this->redirectToRoute('admin_media_index');
            }

            //clones media object
            $originalFile = clone $media;

            // checks if media data has changed
            if($originalFile->getMediaData() !== $media->getMediaData())
            {
                //iterates through all playlists and sets updated at to current time
                foreach ($media->getPlaylistMedia() as $playlistMedia) {
                    $playlist =$playlistMedia->getPlaylist();
                    //sets updated at to current time
                    $playlist->setUpdatedAt(new \DateTime());
                    $this->entityManager->persist($playlist);
                }
            }

            //removes media from db
            $this->entityManager->remove($media);
            $this->entityManager->flush();

            //deletes multimedia file
            if($originalFile->getMediaType() === MediaTypeEnum::IMAGE)
            {
                $this->fileService->deleteMultimediaFile($originalFile);
            }

            //returns success message
            $this->addFlash(
                'success',
                'Multimédium bylo úspěšně smazáno.'
            );
        }catch (Exception $exception){
            //in case of exception returns message
            $this->addFlash(
                'error',
                'Nastala neočekávaná vyjímka: '.$exception->getMessage()
            );
        }
        return $this->redirectToRoute('admin_media_index');
    }
}
