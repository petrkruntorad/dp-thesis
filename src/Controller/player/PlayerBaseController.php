<?php

namespace App\Controller\player;

use App\Entity\Device;
use App\Repository\DeviceRepository;
use App\Services\DeviceService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/player', name: 'player_')]
class PlayerBaseController extends AbstractController
{
    public function __construct(
        private readonly DeviceRepository $repository,
        private readonly DeviceService    $deviceService,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/{unique_hash}', name: 'index')]
    public function index(#[MapEntity(expr: 'repository.getDeviceByHash(unique_hash)')] ?Device $device = null)
    {
        $status = null;
        if (!$device) {
            $status = 'Zařízení nebylo nalezeno';
        }

        if ($device && !$device->getPlaylist()) {
            $status = 'Pro toto zařízení nebyl nalezen playlist';
        }

        return $this->render('player/player.html.twig', [
            'status' => $status,
            'device' => $device
        ]);
    }

    #[Route('/{unique_hash}/playlist/get', name: 'get_playlist')]
    public function getPlaylist(#[MapEntity(expr: 'repository.getDeviceByHash(unique_hash)')] ?Device $device)
    {
        $jsonPlaylist = $this->deviceService->generatePlaylistForDevice($device);

        //inits the response with generated json
        $response = new Response($jsonPlaylist);

        //returns response
        return $response;
    }

    #[Route('/{unique_hash}/config/get', name: 'get_config')]
    public function getConfig(#[MapEntity(expr: 'repository.getDeviceByHash(unique_hash)')] ?Device $device)
    {
        //generates json config for device
        $jsonPlaylist = $this->deviceService->generateConfigForDevice($device);

        //inits the response from generated json
        $response = new Response($jsonPlaylist);

        //inits the disposition of file
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'config.json'
        );

        //sets header
        $response->headers->set('Content-Disposition', $disposition);

        //returns response from json
        return $response;
    }

    #[Route('/get-software/{unique_hash}', name: 'get_software')]
    public function getSoftware(#[MapEntity(expr: 'repository.getDeviceByHash(unique_hash)')] ?Device $device)
    {
        //inits array with all files that should be included in zip file
        $files = array("init_player.py", "init_device.py", "functions.py",
            "refresh_browser.py", "update_config.py", "requirements.txt", "check_player_status.py", "update_device_details.py");

        //inits ZipArchive
        $zip = new \ZipArchive();

        //defines zip name
        $filename = "install.zip";

        //opens specified zip file
        $zip->open($filename,  \ZipArchive::CREATE);

        //generates json config for device
        $jsonPlaylist = $this->deviceService->generateConfigForDevice($device);

        //Adds generated config from database to JSON file and then adds JSON to zip file
        $zip->addFromString("config/config.json", $jsonPlaylist);
        foreach ($files as $file) {
            $zip->addFile($this->getParameter('player_software_directory').'/'.basename($file), $file);
        }

        //closes file
        $zip->close();

        //sets response with all headers
        $response = new Response(file_get_contents($filename));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Content-length', filesize($filename));

        //deletes the temp file for download
        @unlink($filename);

        return $response;
    }


    #[Route('/device/update-details/{unique_hash}', name: 'update_details')]
    public function updateDetails(#[MapEntity(expr: 'repository.getDeviceByHash(unique_hash)')] Device $device)
    {
        try {

            //checks if ruqired variables are defined
            if (!$_POST['ipAddress'] || !$_POST['diskUsage'] || !$_POST['diskCapacity'])
            {
                throw new Exception("Missing parameters in request.");
            }

            //assigns values from post to variables
            $ipAddress = strval($_POST['ipAddress']);
            $diskUsage = strval($_POST['diskUsage']);
            $diskCapacity = strval($_POST['diskCapacity']);

            //checks if device first connection is not set and set current datetime
            if (!$device->getFirstConnection())
            {
                $device->setFirstConnection(new \DateTime("now"));
            }
            //sets current datetime as last connection
            $device->setLastConnection(new \DateTime("now"));
            //sets new values to device
            $device->setLocalIpAddress($ipAddress);
            $device->setDiskUsage($diskUsage);
            $device->setDiskCapacity($diskCapacity);

            //saves changes
            $this->entityManager->persist($device);
            $this->entityManager->flush();

            return new Response('Player data changed successfully');
        }
        catch (Exception $exception)
        {
            return new Response('Internal server error', 500);
        }
    }
}
