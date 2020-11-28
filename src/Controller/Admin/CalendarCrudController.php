<?php

namespace App\Controller\Admin;

use App\Entity\Calendar;
use App\Entity\Scene;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            ->addFormTheme('form/custom_types.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            ImageField::new('imageFile')
                ->setLabel('Background image')
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
        $layOut = Action::new('LayOut')
            ->addCssClass('btn')
            ->addCssClass('btn-secondary')
            ->linkToCrudAction('layOut');

        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action->displayIf(fn (Calendar $calendar) => $this->isGranted(CalendarVoter::EDIT, $calendar)))
            ->add(Crud::PAGE_INDEX, $show)
            ->add(Crud::PAGE_EDIT, $show)
            ->add(Crud::PAGE_EDIT, $layOut);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('build/admin/calendar.css')
            ->addJsFile('build/admin/calendar.js');
    }

    public function layOut(AdminContext $context)
    {
        $request = $context->getRequest();

        /** @var Calendar $calendar */
        $calendar = $context->getEntity()->getInstance();
        $cropBoxes = $calendar->getScenes()->map(static fn (Scene $scene) => $scene->getCropBoxAsArray())->toArray();

        $form = $this->createFormBuilder()
            ->add('columns', IntegerType::class, [
                'data' => 6,
            ])
            ->add('width', IntegerType::class, [
                'data' => 200,
            ])
            ->add('height', IntegerType::class, [
                'data' => 200,
            ])
            ->add('shuffle', CheckboxType::class, [
                'data' => true,
            ])
            ->add('cropBoxes', HiddenType::class, [
                'required' => false,
                'data' => json_encode($cropBoxes),
            ])
            ->add('save', SubmitType::class)
            ->add('layOut', ButtonType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cropBoxes = json_decode($form->get('cropBoxes')->getData(), true);
            foreach ($calendar->getScenes() as $index => $scene) {
                $scene->setCropBox(json_encode($cropBoxes[$index] ?? null, JSON_THROW_ON_ERROR));
            }
            $this->persistEntity($this->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()),
                $calendar);

            $url = $context->getReferrer()
                ?? $this->get(CrudUrlGenerator::class)->build()->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return $this->render('admin/actions/calendar/lay_out.html.twig', [
            'form' => $form->createView(),
            'crop_boxes' => $cropBoxes,
            'calendar' => $calendar,
        ]);
    }
}
