<?php

namespace App\Form\admin;

use App\Entity\Device;
use App\Entity\Playlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Název',
            ])
            ->add('location', TextType::class, [
                'label' => 'Umístění',
            ])
            ->add('playlist', EntityType::class, [
                'label' => 'Playlist',
                'class' => Playlist::class,
                'choice_label' => function (Playlist $choiceLabel): string {
                    return $choiceLabel->getName();
                },
                'autocomplete' => true,
                'placeholder' => 'Vyberte playlist',
            ])
            ->add('save', SubmitType::class,[
                'label'=>'Uložit',
                'attr'=> [
                    'class'=> 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Device::class,
        ]);
    }
}
