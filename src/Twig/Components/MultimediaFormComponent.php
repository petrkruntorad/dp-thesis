<?php

namespace App\Twig\Components;

use App\Entity\Media;
use App\Form\admin\MediaFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(method: 'get')]
class MultimediaFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?Media $media = null;

    public function mount(?Media $media = null)
    {
        $this->media = $media ?: new Media();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            MediaFormType::class
        );
    }
}
