<?php

namespace App\Controller;

use App\Entity\Julekalender;
use App\Entity\Laage;
use App\Repository\JulekalenderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/julekalender")
 */
class JulekalenderController extends AbstractController
{
    /**
     * @Route("")
     */
    public function index(JulekalenderRepository $repository): Response
    {
        return $this->show($repository->findOneBy([]));
    }

    /**
     * @Route("/{julekalender}", name="julekalender_show")
     */
    public function show(Julekalender $julekalender): Response
    {
        $config = [
            'scenes' => $julekalender->getLaager()->map(fn (Laage $laage) => [
                'content' => $laage->getContent(),
                'configuration' => $laage->getConfigurationAsArray(),
            ])->toArray(),
        ];

        return $this->render('julekalender/show.html.twig', [
            'julekalender' => $julekalender,
            'config' => $config,
        ]);
    }

    /**
     * @Route("/{julekalender}/styles", name="julekalender_styles")
     */
    public function styles(Julekalender $julekalender): Response
    {
        $response = new Response();
        $response->headers->set('content-type', 'text/css');

        return $this->render('julekalender/styles.css.twig', [
            'images_base_url' => $this->getParameter('app.images.base_url'),
            'julekalender' => $julekalender,
        ], $response);
    }

    /**
     * @Route("/{julekalender}/layout", name="julekalender_layout")
     */
    public function layout(Julekalender $julekalender): Response
    {
        $this->addFlash('warning', __METHOD__);

        return $this->redirectToRoute('admin', [
            'crudAction' => 'edit',
            'entityId' => $julekalender->getId(),
        ]);
    }
}
