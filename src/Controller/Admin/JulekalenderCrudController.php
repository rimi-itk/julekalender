<?php

namespace App\Controller\Admin;

use App\Entity\Julekalender;
use App\Entity\Laage;
use App\Form\LaageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class JulekalenderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Julekalender::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            CollectionField::new('laager')
                ->setEntryIsComplex(true)
                ->setEntryType(LaageType::class)
        ];
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
//            ->addJsFile('build/runtime.js')
            ->addJsFile('build/admin/julekalender.js')
            ;
    }
}
