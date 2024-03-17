<?php

namespace App\Form\admin;

use App\Entity\Media;
use App\Entity\PlaylistMedia;
use App\Repository\PlaylistMediaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class PlaylistMediaFormType extends AbstractType
{
    public function __construct(
        private readonly PlaylistMediaRepository $playlistMediaRepository,
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('media', EntityType::class, [
                'label' => 'Multimédium',
                'class' => Media::class,
                'choice_label' => function (Media $choiceLabel): string {
                    return $choiceLabel->getName();
                },
                'autocomplete' => true,
                'placeholder' => 'Vyberte multimédium',
            ])
            ->add('customTime', CheckboxType::class, [
                'label' => 'Vlastní doba přehrávání',
                'help' => '* Pokud není zaškrtnuto, vybrané multimédium se bude zobrazovat celý den.',
                'help_attr' => [
                    'class' => 'text-muted',
                ],
                'required' => false,
                'constraints' => [
                    new Callback([$this, 'validateCustomTimeAvailability']),
                    new Callback([$this, 'validateCustomTime']),
                ]
            ])
            ->add('save', SubmitType::class,[
                'label'=>'Uložit',
                'attr'=> [
                    'class'=> 'btn btn-primary',
                ],
            ])
        ;

        $builder->addDependent('showFrom', ['customTime'], function(DependentField $field, ?bool $customTime = false) {
            if (!$customTime) {
                return;
            }
            $field->add(TimeType::class, [
                'label' => 'Zobrazit od',
                'input' => 'datetime',
                'widget' => 'single_text',
                'constraints' => [
                    new Callback([$this, 'validateShowTime']),
                    new Callback([$this, 'validateTimeSlotAvailability']),
                ]
            ]);
        });

        $builder->addDependent('showTo', ['customTime'], function(DependentField $field, ?bool $customTime = false) {
            if (!$customTime) {
                return;
            }
            $field->add(TimeType::class, [
                'label' => 'Zobrazit do',
                'input' => 'datetime',
                'widget' => 'single_text',
                'constraints' => [
                    new Callback([$this, 'validateShowTime']),
                    new Callback([$this, 'validateTimeSlotAvailability']),
                ]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlaylistMedia::class,
        ]);
    }

    public function validateCustomTime($data, ExecutionContextInterface $context)
    {
        $playlistMedia = $context->getRoot()->getData();

        if(!$playlistMedia->isCustomTime())
        {
            return;
        }
        if($playlistMedia->getId() === null)
        {
            $mediaWithSpecifiedTime = $this->playlistMediaRepository->getMediaForPlaylistByCustomTimeAndMedia($playlistMedia->getPlaylist());
        }else{
            $mediaWithSpecifiedTime = $this->playlistMediaRepository->getMediaForPlaylistByCustomTimeAndMedia($playlistMedia->getPlaylist(), false, $playlistMedia->getMedia());
        }

        if(count($mediaWithSpecifiedTime) > 0)
        {
            $context->buildViolation('Nelze přidat multimédium, které se má přehrávat v celém časovém úseku, protože existují multimédia, které se mají přehrávat v určitém časovém úseku.')
                ->atPath('customTime')
                ->addViolation();
        }
    }

    public function validateCustomTimeAvailability($data, ExecutionContextInterface $context)
    {
        $playlistMedia = $context->getRoot()->getData();

        if($playlistMedia->isCustomTime())
        {
            return;
        }

        if($playlistMedia->getId() === null)
        {
            $allMediaForPlaylist = $this->playlistMediaRepository->getMediaForPlaylistByCustomTimeAndMedia($playlistMedia->getPlaylist(), true);
        }else{
            $allMediaForPlaylist = $this->playlistMediaRepository->getMediaForPlaylistByCustomTimeAndMedia($playlistMedia->getPlaylist(), true, $playlistMedia->getMedia());
        }

        if(count($allMediaForPlaylist) > 0)
        {
            $context->buildViolation('Nelze nastavit vlastní čas pro multimédium, protože existuje multimédium, které se má přehrávat v celém časovém úseku.')
                ->atPath('customTime')
                ->addViolation();
        }
    }

    public function validateShowTime($data, ExecutionContextInterface $context)
    {
        $playlistMedia = $context->getRoot()->getData();

        if($playlistMedia->getShowFrom() && $playlistMedia->getShowTo())
        {
            if (strtotime($playlistMedia->getShowFrom()->format('H:i:s')) >= strtotime($playlistMedia->getShowTo()->format('H:i:s')))
            {
                $context->buildViolation('Čas zobrazení multimédia "od" musí být menší než čas zobrazení multimédia "do".')
                    ->atPath($context->getObject()->getName())
                    ->addViolation();
            }
        }
    }

    public function validateTimeSlotAvailability($data, ExecutionContextInterface $context)
    {
        $playlistMedia = $context->getRoot()->getData();

        $mediaForSpecifiedTimeSlot = $this->playlistMediaRepository->getMediaForPlaylistByShowTimeAndMedia($playlistMedia->getPlaylist(), $playlistMedia->getShowFrom(), $playlistMedia->getShowTo(), $playlistMedia);

        if(count($mediaForSpecifiedTimeSlot) > 0)
        {
            $context->buildViolation('Nelze přidat multimédium, které se má přehrávat v určitém časovém úseku, protože existuje multimédium, které se má přehrávat v tomto časovém úseku.')
                ->atPath($context->getObject()->getName())
                ->addViolation();
        }
    }
}
