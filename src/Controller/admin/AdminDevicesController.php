<?php

namespace App\Controller\admin;

use App\Entity\Device;
use App\Entity\User;
use App\Form\admin\DeviceFormType;
use App\Repository\DeviceRepository;
use App\Services\DeviceService;
use App\Services\HashService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/admin/devices', name: 'admin_devices_')]
class AdminDevicesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeviceRepository $deviceRepository,
        private readonly PaginatorInterface $paginator,
        private readonly DeviceService $deviceService,
    )
    {
    }
    #[Route('/', name: 'index')]
    public function index(Request $request)
    {
        // gets devices from db as query
        $devices = $this->deviceRepository->getDevicesAsQuery();
        // paginates devices
        $paginator = $this->paginator->paginate($devices, $request->query->getInt('page', 1), 20, ['distinct' => false]);
        return $this->render('admin/devices/index.html.twig', [
            'devices' => $paginator,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        //form init
        $form = $this->createForm(DeviceFormType::class);
        $form->handleRequest($request);

        //if form is submitted and is valid by values on the backend
        if($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $device = $form->getData();
                $device->setUniqueHash($this->deviceService->getUniqueHashForDevice());
                //saves device to database
                $this->entityManager->persist($device);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Zařízení s názvem: '.$device->getName().' bylo úspěšně přidáno.'
                );

                //redirects to devices overview
                return $this->redirectToRoute('admin_devices_index');
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

        return $this->render('admin/devices/create.html.twig', [
            'form' => $form->createView(),

        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(Request $request, Device $device)
    {
        //form init
        $form = $this->createForm(DeviceFormType::class, $device);

        $form->handleRequest($request);

        //if form is submitted and is valid by values on the backend
        if($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $device = $form->getData();

                //saves changes to db
                $this->entityManager->persist($device);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Zařízení bylo úspěšně upraveno.'
                );

                //redirects to devices overview
                return $this->redirectToRoute('admin_devices_index');
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

        return $this->render('admin/devices/update.html.twig', [
            'form' => $form->createView(),
            'device' => $device,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Device $device)
    {
        try {
            // removes device from db
            $this->entityManager->remove($device);

            // flushes changes
            $this->entityManager->flush();

            //returns success message
            $this->addFlash(
                'success',
                'Zařízení bylo úspěšně smazáno.'
            );
        }catch (Exception $exception){

            //in case of exception returns message
            $this->addFlash(
                'error',
                'Nastala neočekávaná vyjímka: '.$exception->getMessage()
            );
        }
        // redirects to devices overview
        return $this->redirectToRoute('admin_devices_index');
    }
}
