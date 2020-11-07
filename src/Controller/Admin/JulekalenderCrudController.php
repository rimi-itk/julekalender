<?php

namespace App\Controller\Admin;

use App\Entity\Julekalender;
use App\Form\LaageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class JulekalenderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Julekalender::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            ImageField::new('imageFile')
                ->onlyOnForms()
                ->setFormType(VichImageType::class)
                ->setFormTypeOptions([
                    'allow_delete' => false,
                ]),
            CollectionField::new('laager')
                ->setLabel('Låger')
                ->setEntryIsComplex(true)
                ->setEntryType(LaageType::class),
            DateTimeField::new('createdAt')->onlyOnIndex(),
            DateTimeField::new('UpdatedAt')->onlyOnIndex(),
            TextareaField::new('configuration')->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $show = Action::new('Show')
            ->linkToRoute('julekalender_show', fn (Julekalender $julekalender) => ['julekalender' => $julekalender->getId()]);
        $layout = Action::new('Layout')
            ->linkToRoute('julekalender_layout', fn (Julekalender $julekalender) => ['julekalender' => $julekalender->getId()]);

        return $actions
            ->add(Crud::PAGE_INDEX, $show)
            ->add(Crud::PAGE_EDIT, $show)
            ->add(Crud::PAGE_EDIT, $layout);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('build/admin/julekalender.css')
            ->addJsFile('build/admin/julekalender.js');
    }
}
