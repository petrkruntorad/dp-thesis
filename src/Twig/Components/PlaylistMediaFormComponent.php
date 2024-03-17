<?php

namespace App\Twig\Components;

use App\Entity\Playlist;
use App\Entity\PlaylistMedia;
use App\Form\admin\PlaylistMediaFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
final class PlaylistMediaFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?PlaylistMedia $playlistMedia = null;

    #[LiveProp(writable: true)]
    public ?Playlist $playlist = null;

    public function hasValidationErrors(): bool
    {
        return $this->getForm()->isSubmitted() && !$this->getForm()->isValid();
    }
    protected function instantiateForm(): FormInterface
    {

        $this->playlistMedia->setPlaylist($this->playlist);
        return $this->createForm(
            PlaylistMediaFormType::class,
            $this->playlistMedia
        );
    }
}
