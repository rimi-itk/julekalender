<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CropBoxType extends AbstractType
{
    /** @var array */
    private $options;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['image_field_selector']);
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['options'] = [
            'image_field_selector' => $options['image_field_selector'] ?? null,
        ];
    }
}
