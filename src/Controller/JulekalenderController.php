<?php

namespace App\Controller;

use App\Entity\Julekalender;
use App\Entity\Laage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/julekalender")
 */
class JulekalenderController extends AbstractController
{
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
}
