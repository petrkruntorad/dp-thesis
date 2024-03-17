<?php

namespace App\Form\admin;

use App\Entity\Media;
use App\Enum\MediaTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class MediaFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface   $translator,
    )
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        $isEdit = $options['is_edit'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Název',
            ])
            ->add('mediaType', EnumType::class, [
                'label' => 'Typ média',
                'class' => MediaTypeEnum::class,
                'autocomplete' => true,
                'placeholder' => 'Vyberte typ',
                'choice_label' => function (MediaTypeEnum $choice): string {
                    return $choice->trans($this->translator);
                },
            ])
            ->add('save', SubmitType::class,[
                'label'=>'Uložit',
                'attr'=> [
                    'class'=> 'btn btn-primary',
                ],
            ])
        ;

        $builder->addDependent('mediaData', ['mediaType'], function(DependentField $field, ?MediaTypeEnum $mediaType) use ($isEdit) {
            if (null === $mediaType) {
                return;
            }
            if($mediaType === MediaTypeEnum::IMAGE) {
                $field->add( DropzoneType::class, [
                    'label' => 'Mutlimédium',
                    'required' => !$isEdit,
                    'mapped' => false,
                    'help' => 'Podporované typy souborů: .jpg, .png, .jpeg, .webp, .gif a maximální velikost souboru je 50MB.',
                    'attr' => [
                        'accept' => "image/png, image/jpeg, image/jpg, image/webp, image/gif",
                        'placeholder' => 'Přetáhněte soubor nebo klikněte pro nahrání',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '50M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'image/webp',
                                'image/gif'
                            ],
                            'mimeTypesMessage' => 'Prosím nahrajte obrázek ve formátu JPG, PNG, JPEG, WEBP nebo GIF.',
                        ])
                    ],
                ]);
            }elseif($mediaType === MediaTypeEnum::VIDEO){
                $field->add( DropzoneType::class, [
                    'label' => 'Mutlimédium',
                    'required' => !$isEdit,
                    'mapped' => false,
                    'help' => 'Podporované typy souborů: .mp4 a maximální velikost souboru je 1024 MB.',
                    'attr' => [
                        'accept' => "video/mp4",
                        'placeholder' => 'Přetáhněte soubor nebo klikněte pro nahrání',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '1024M',
                            'mimeTypes' => [
                                'video/mp4',
                            ],
                            'mimeTypesMessage' => 'Prosím nahrajte video ve formátu MP4.',
                        ])
                    ],
                ]);
            } else {
                $field ->add(TextType::class, [
                    'label' => 'Zdroj pro multimédium',
                    'help' => 'Zadejte URL adresu zdroje, která se nachází pod tlačítkem sdílet u youtube videa. Zde se nachází záložka "Vložit" a poté stačí zkopírovat URL adresu nacházející se u atributu src.',
                    'constraints' => [
                        new Callback([$this, 'validateCrossSiteOrigin']),
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_edit' => false,
        ]);
    }

    public function validateUrl($data, ExecutionContextInterface $context)
    {
        $media = $context->getRoot()->getData();

        if($media->getMediaType() !== MediaTypeEnum::WEBSITE)
        {
            return;
        }

        if(!filter_var($data, FILTER_VALIDATE_URL))
        {
            $context->buildViolation('Zadaná adresa není platná URL.')
                ->atPath('mediaData')
                ->addViolation();
        }
    }

    public function validateCrossSiteOrigin($data, ExecutionContextInterface $context)
    {
        // gets media object
        $media = $context->getRoot()->getData();

        // checks if media type is website
        if($media->getMediaType() !== MediaTypeEnum::WEBSITE)
        {
            return;
        }
        // gets url
        $url = $media->getMediaData();
        // Remove all illegal characters from a url
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Validates url
        if(!filter_var($url, FILTER_VALIDATE_URL))
        {
            $context->buildViolation('Zadaná adresa není platná URL. Platná URL musí obsahovat protokol (http/https).')
                ->atPath('mediaData')
                ->addViolation();

            return;
        }

        if($mediaData = $media->getMediaData())
        {
            // gets headers from url
            $pageHeaders = get_headers($mediaData, true);

            // checks if URL returns 200 OK
            if($pageHeaders[0] !== 'HTTP/1.1 200 OK')
            {
                $context->buildViolation('Zadaná adresa neexistuje nebo není dostupná.')
                    ->atPath('mediaData')
                    ->addViolation();
            }

            // checks if URL has x-frame-options header
            if(array_key_exists('x-frame-options', $pageHeaders))
            {
                // checks if x-frame-options header is set to DENY or SAMEORIGIN
               if($pageHeaders['x-frame-options'] === 'DENY' || $pageHeaders['x-frame-options'] === 'SAMEORIGIN')
               {
                   $context->buildViolation('Tento zdroj nepodporuje vložení do iframe. Prosím zvolte jiný zdroj.')
                       ->atPath('mediaData')
                       ->addViolation();
               }
            }

            // checks if URL has X-Frame-Options header
            if(array_key_exists('X-Frame-Options', $pageHeaders))
            {
                // checks if X-Frame-Options header is set to DENY or SAMEORIGIN
                if($pageHeaders['X-Frame-Options'] === 'DENY' || $pageHeaders['X-Frame-Options'] === 'SAMEORIGIN')
                {
                    $context->buildViolation('Tento zdroj nepodporuje vložení do iframe. Prosím zvolte jiný zdroj.')
                        ->atPath('mediaData')
                        ->addViolation();
                }
            }
        }
    }
}
