<?php

namespace App\Dto;

class PlayerConfigDto
{
    private ?string $uniqueHash = null;
    private ?string $getConfigUrl = null;
    private ?string $playerUrl = null;
    private ?string $updateDeviceDetailsUrl = null;
    private ?string $configUpdateTimeCron = '0 * * * *'; // inits variable with cron that updates config every hour
    private ?string $checkIfPlayerIsRunningTimeCron = '*/5 * * * *'; // inits variable with cron that checks if player is running every 5 minutes
    private ?string $restartPlayerTimeCron = '0 0 * * *'; // inits variable with cron that restarts player every day at midnight
    private ?string $updateDeviceDetails = '0 0 * * *'; // inits variable with cron that updates device details every day at midnight
    private ?string $startPlayer = '@reboot'; // inits variable with cron that starts player on reboot

    public function getUniqueHash(): ?string
    {
        return $this->uniqueHash;
    }

    public function setUniqueHash(?string $uniqueHash): void
    {
        $this->uniqueHash = $uniqueHash;
    }

    public function getGetConfigUrl(): ?string
    {
        return $this->getConfigUrl;
    }

    public function setGetConfigUrl(?string $getConfigUrl): void
    {
        $this->getConfigUrl = $getConfigUrl;
    }

    public function getPlayerUrl(): ?string
    {
        return $this->playerUrl;
    }

    public function setPlayerUrl(?string $playerUrl): void
    {
        $this->playerUrl = $playerUrl;
    }

    public function getConfigUpdateTimeCron(): ?string
    {
        return $this->configUpdateTimeCron;
    }

    public function setConfigUpdateTimeCron(?string $configUpdateTimeCron): void
    {
        $this->configUpdateTimeCron = $configUpdateTimeCron;
    }

    public function getCheckIfPlayerIsRunningTimeCron(): ?string
    {
        return $this->checkIfPlayerIsRunningTimeCron;
    }

    public function setCheckIfPlayerIsRunningTimeCron(?string $checkIfPlayerIsRunningTimeCron): void
    {
        $this->checkIfPlayerIsRunningTimeCron = $checkIfPlayerIsRunningTimeCron;
    }

    public function getRestartPlayerTimeCron(): ?string
    {
        return $this->restartPlayerTimeCron;
    }

    public function setRestartPlayerTimeCron(?string $restartPlayerTimeCron): void
    {
        $this->restartPlayerTimeCron = $restartPlayerTimeCron;
    }

    public function getUpdateDeviceDetails(): ?string
    {
        return $this->updateDeviceDetails;
    }

    public function setUpdateDeviceDetails(?string $updateDeviceDetails): void
    {
        $this->updateDeviceDetails = $updateDeviceDetails;
    }

    public function getUpdateDeviceDetailsUrl(): ?string
    {
        return $this->updateDeviceDetailsUrl;
    }

    public function setUpdateDeviceDetailsUrl(?string $updateDeviceDetailsUrl): void
    {
        $this->updateDeviceDetailsUrl = $updateDeviceDetailsUrl;
    }

    public function getStartPlayer(): ?string
    {
        return $this->startPlayer;
    }

    public function setStartPlayer(?string $startPlayer): void
    {
        $this->startPlayer = $startPlayer;
    }
}
