<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Scene;
use App\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/calendar")
 */
class CalendarController extends AbstractController
{
    /**
     * @Route("")
     */
    public function index(CalendarRepository $repository): Response
    {
        return $this->show($repository->findOneBy([]));
    }

    /**
     * @Route("/{calendar}", name="calendar_show")
     */
    public function show(Calendar $calendar): Response
    {
        $config = [
            'scenes' => $calendar->getScenes()->map(fn (Scene $scene) => [
                'content' => $scene->getContent(),
                'configuration' => $scene->getConfigurationAsArray(),
            ])->toArray(),
        ];

        return $this->render('calendar/show.html.twig', [
            'calendar' => $calendar,
            'config' => $config,
        ]);
    }

    /**
     * @Route("/{calendar}/styles", name="calendar_styles")
     */
    public function styles(Calendar $calendar): Response
    {
        $response = new Response();
        $response->headers->set('content-type', 'text/css');

        return $this->render('calendar/styles.css.twig', [
            'images_base_url' => $this->getParameter('app.images.base_url'),
            'calendar' => $calendar,
        ], $response);
    }

    /**
     * @Route("/{calendar}/layout", name="calendar_layout")
     */
    public function layout(Calendar $calendar): Response
    {
        $this->addFlash('warning', __METHOD__);

        return $this->redirectToRoute('admin', [
            'crudAction' => 'edit',
            'entityId' => $calendar->getId(),
        ]);
    }
}
