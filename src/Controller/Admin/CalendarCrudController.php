<?php

namespace App\Controller\Admin;

use App\Entity\Calendar;
use App\Form\SceneType;
use App\Security\Voter\CalendarVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CalendarCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Calendar::class;
    }

    public function index(AdminContext $context)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return parent::index($context);
    }

    public function edit(AdminContext $context)
    {
        $this->denyAccessUnlessGranted(
            CalendarVoter::EDIT,
            $context->getEntity()->getInstance(),
            'You do not have permission to edit this calendar.'
        );

        return parent::edit($context);
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
            CollectionField::new('scenes')
                ->setEntryIsComplex(true)
                ->setEntryType(SceneType::class),
            AssociationField::new('createdBy')->onlyOnIndex(),
            DateTimeField::new('createdAt')->onlyOnIndex(),
            DateTimeField::new('UpdatedAt')->onlyOnIndex(),
            TextareaField::new('configuration')->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $show = Action::new('Show')
            ->linkToRoute('calendar_show', fn (Calendar $calendar) => ['calendar' => $calendar->getId()]);

        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action->displayIf(fn (Calendar $calendar) => $this->isGranted(CalendarVoter::EDIT, $calendar)))
            ->add(Crud::PAGE_INDEX, $show)
            ->add(Crud::PAGE_EDIT, $show);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('build/admin/calendar.css')
            ->addJsFile('build/admin/calendar.js');
    }
}
