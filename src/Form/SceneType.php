<?php

namespace App\Form;

use App\Entity\Scene;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SceneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('position', HiddenType::class)
            ->add('content', TextareaType::class, [
                'required' => true,
            ])
            ->add('doNotOpenUntil', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('imageFile', VichImageType::class)
            ->add('configuration', YamlType::class, [
                'required' => true,
            ])
            ->add('cropBox', CropBoxType::class, [
                'image_field_selector' => '.field-image',
            ])
            ->add('openedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Scene::class,
        ]);
    }
}
