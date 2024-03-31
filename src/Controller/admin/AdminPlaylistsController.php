<?php

namespace App\Controller\admin;

use App\Entity\Playlist;
use App\Entity\PlaylistMedia;
use App\Form\admin\PlaylistFormType;
use App\Form\admin\PlaylistMediaFormType;
use App\Repository\DeviceRepository;
use App\Repository\PlaylistMediaRepository;
use App\Repository\PlaylistRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/playlists', name: 'admin_playlists_')]
class AdminPlaylistsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface  $entityManager,
        private readonly PlaylistRepository      $playlistRepository,
        private readonly PlaylistMediaRepository $playlistMediaRepository,
        private readonly PaginatorInterface      $paginator,
        private readonly DeviceRepository        $deviceRepository
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request)
    {
        //gets playlists as query
        $playlists = $this->playlistRepository->getPlaylistsAsQuery();
        //paginates playlists
        $paginator = $this->paginator->paginate($playlists, $request->query->getInt('page', 1), 20, ['distinct' => false]);
        return $this->render('admin/playlists/index.html.twig', [
            'playlists' => $paginator,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        //form init
        $form = $this->createForm(PlaylistFormType::class);
        $form->handleRequest($request);

        //if form is submitted and is valid by values on the backend
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $playlist = $form->getData();

                //saves playlist to database
                $this->entityManager->persist($playlist);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Přehrávací seznam s názvem: ' . $playlist->getName() . ' byl úspěšně přidán.'
                );

                //redirects to playlist overview
                return $this->redirectToRoute('admin_playlists_index');
            } catch (Exception $exception) {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: ' . $exception->getMessage()
                );
            }
        }

        return $this->render('admin/playlists/create.html.twig', [
            'form' => $form->createView(),

        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(Request $request, Playlist $playlist)
    {
        //form init
        $form = $this->createForm(PlaylistFormType::class, $playlist);

        $form->handleRequest($request);

        //if form is submitted and is valid by values on the backend
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $playlist = $form->getData();

                //sets updated at to current time
                $playlist->setUpdatedAt(new DateTime());

                //saves changes to db
                $this->entityManager->persist($playlist);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Přehrávací seznam byl úspěšně upraven.'
                );

                //redirects to playlist overview
                return $this->redirectToRoute('admin_playlists_index');
            } catch (Exception $exception) {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: ' . $exception->getMessage()
                );
            }
        }

        return $this->render('admin/playlists/update.html.twig', [
            'form' => $form->createView(),
            'playlist' => $playlist,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Playlist $playlist)
    {
        try {
            // finds every relation between playlist and device
            $relatedPlaylist = $this->deviceRepository->findBy(['playlist' => $playlist]);
            // if playlist is assigned to any device, returns error message
            if(count($relatedPlaylist) > 0){
                $this->addFlash(
                    'error',
                    'Nelze smazat přehrávací seznam, který je přiřazen k zařízení.'
                );
                return $this->redirectToRoute('admin_playlists_index');
            }

            // finds every relation between playlist and media and removes it
            $relatedPlaylistMedia = $this->playlistMediaRepository->findBy(['playlist' => $playlist]);
            foreach ($relatedPlaylistMedia as $media) {
                $this->entityManager->remove($media);
            }

            // removes playlist from database
            $this->entityManager->remove($playlist);

            // saves changes
            $this->entityManager->flush();

            // returns success message
            $this->addFlash(
                'success',
                'Přehrávací seznam byl úspěšně smazán.'
            );
        } catch (Exception $exception) {
            // in case of exception returns message
            $this->addFlash(
                'error',
                'Nastala neočekávaná vyjímka: ' . $exception->getMessage()
            );
        }
        // redirects to playlist overview
        return $this->redirectToRoute('admin_playlists_index');
    }

    #[Route('/detail/{id}', name: 'show')]
    public function show(Request $request, Playlist $playlist)
    {
        //playlistmedia form init
        $emptyPlaylistMedia = new PlaylistMedia();
        $emptyPlaylistMedia->setPlaylist($playlist);

        // initializes form
        $form = $this->createForm(PlaylistMediaFormType::class, $emptyPlaylistMedia);

        //handles request
        $form->handleRequest($request);

        // if form is submitted and is valid by values on the backend
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $playlistMedia = $form->getData();

                //checks if custom time is not used
                if (!$playlistMedia->isCustomTime()) {
                    //sets show from to midnight and show to to one second before midnight
                    // to prevent one minute without content till midnight
                    $secondBeforeMidnight = (new DateTime('midnight'))->modify('+1 day')->modify('-1 second');
                    $playlistMedia->setShowFrom(new DateTime('midnight'));
                    $playlistMedia->setShowTo($secondBeforeMidnight);
                }

                //checks if custom time is used
                if ($playlistMedia->isCustomTime()) {
                    //gets time with one minute before midnight
                    $minuteBeforeMidnight = (new DateTime('midnight'))->modify('+1 day')->modify('-1 minute');
                    //check if show to is set to one minute before midnight and adds 59 seconds to it to prevent one minute without content till midnight sinc show to can be set max to 23:59
                    if (strtotime($playlistMedia->getShowTo()->format('H:i:s')) == strtotime(($minuteBeforeMidnight)->format('H:i:s'))) {
                        $playlistMedia->setShowTo(($playlistMedia->getShowTo())->modify('+1 minute')->modify('-1 second'));
                    }
                }

                //sets updated at to current time
                $playlist->setUpdatedAt(new DateTime());
                //persists changes for playlist
                $this->entityManager->persist($playlist);

                //saves playlist media to database
                $this->entityManager->persist($playlistMedia);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Multimédium bylo úspěšně přidáno do přehrávacího seznamu ' . $playlist->getName() . '.'
                );

                //redirects to playlist detail
                return $this->redirectToRoute('admin_playlists_show', ['id' => $playlist->getId()]);
            } catch (Exception $exception) {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: ' . $exception->getMessage()
                );
            }
        }
        //gets playlist media as query
        $playlistMediaAsQuery = $this->playlistMediaRepository->getMediaForPlaylistAsQuery($playlist);

        //paginates playlist media
        $paginator = $this->paginator->paginate($playlistMediaAsQuery, $request->query->getInt('page', 1), 20, ['distinct' => false]);

        return $this->render('admin/playlists/detail.html.twig', [
            'playlist_data' => $playlist,
            'playlist_media_paginator' => $paginator,
            'playlist_media' => $emptyPlaylistMedia,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/detail/{id}/edit/{playlist_media_id}', name: 'show_edit_playlist_media')]
    public function show_edit_playlist_media(Request $request, Playlist $playlist, #[MapEntity(id: 'playlist_media_id')] PlaylistMedia $playlistMedia)
    {
        // form init with existing playlist media
        $form = $this->createForm(PlaylistMediaFormType::class, $playlistMedia);

        //handles request
        $form->handleRequest($request);
        // if form is submitted and is valid by values on the backend
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $playlistMedia = $form->getData();

                //checks if custom time is not used
                if (!$playlistMedia->isCustomTime()) {
                    //sets show from to midnight and show to to one second before midnight
                    $secondBeforeMidnight = (new DateTime('midnight'))->modify('+1 day')->modify('-1 second');
                    $playlistMedia->setShowFrom(new DateTime('midnight'));
                    $playlistMedia->setShowTo($secondBeforeMidnight);
                }

                //checks if custom time is used
                if ($playlistMedia->isCustomTime()) {
                    //gets time with one minute before midnight
                    $minuteBeforeMidnight = (new DateTime('midnight'))->modify('+1 day')->modify('-1 minute');
                    //check if show to is set to one minute before midnight and adds 59 seconds to it to prevent one minute without content till midnight sinc show to can be set max to 23:59
                    if (strtotime($playlistMedia->getShowTo()->format('H:i:s')) == strtotime(($minuteBeforeMidnight)->format('H:i:s'))) {
                        $playlistMedia->setShowTo(($playlistMedia->getShowTo())->modify('+1 minute')->modify('-1 second'));
                    }
                }

                //sets updated at to current time
                $playlist->setUpdatedAt(new DateTime());
                //persists changes for playlist
                $this->entityManager->persist($playlist);

                //saves playlist to database
                $this->entityManager->persist($playlistMedia);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Multimédium bylo úspěšně upraveno.'
                );

                //redirects to playlist detail
                return $this->redirectToRoute('admin_playlists_show', ['id' => $playlist->getId()]);
            } catch (Exception $exception) {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: ' . $exception->getMessage()
                );
            }
        }
        // gets playlist media as query
        $playlistMediaAsQuery = $this->playlistMediaRepository->getMediaForPlaylistAsQuery($playlist);

        // paginates playlist media
        $paginator = $this->paginator->paginate($playlistMediaAsQuery, $request->query->getInt('page', 1), 20, ['distinct' => false]);

        return $this->render('admin/playlists/detail.html.twig', [
            'playlist_data' => $playlist,
            'playlist_media_paginator' => $paginator,
            'playlist_media' => $playlistMedia,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/detail/{id}/delete/{playlist_media_id}', name: 'show_delete_playlist_media')]
    public function delete_edit_playlist_media(Playlist $playlist, #[MapEntity(id: 'playlist_media_id')] PlaylistMedia $playlistMedia)
    {
        try {
            //sets updated at to current time
            $playlist->setUpdatedAt(new DateTime());

            //removes playlist media from database
            $this->entityManager->remove($playlistMedia);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Multimédium bylo úspěšně smazáno z přehrávacího seznamu.'
            );
        } catch (Exception $exception) {

            // in case of exception returns message
            $this->addFlash(
                'error',
                'Nastala neočekávaná vyjímka: ' . $exception->getMessage()
            );
        }

        // redirects to playlist detail
        return $this->redirectToRoute('admin_playlists_show', ['id' => $playlist->getId()]);
    }
}
