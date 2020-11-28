<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Scene;
use App\Repository\CalendarRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
            'data_url' => $this->generateUrl('calendar_scenes', ['calendar' => $calendar->getId()]),
            'scene_open_url_template' => $this->generateUrl('calendar_scenes_open', ['calendar' => $calendar->getId(), 'scene' => '{{ id }}']),
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
     * @Route("/{calendar}/scenes", name="calendar_scenes")
     */
    public function scenes(Calendar $calendar, SerializerInterface $serializer): Response
    {
        $scenes = $calendar->getScenes();
        $data = $serializer->serialize(['data' => $this->renderScenes($scenes)], 'json', ['groups' => 'scene']);

        return JsonResponse::fromJsonString($data);
    }

    /**
     * @Route("/{calendar}/scenes/{scene}/open", name="calendar_scenes_open", methods={"PATCH"})
     */
    public function scenesOpen(Calendar $calendar, Scene $scene, EntityManagerInterface $entityManager, SerializerInterface $serializer, TranslatorInterface $translator): Response
    {
        $now = new DateTimeImmutable();
        if (null === $this->getUser()) {
            if ($scene->getDoNotOpenUntil() && $now < $scene->getDoNotOpenUntil()) {
                // @see https://jsonapi.org/examples/#error-objects
                return new JsonResponse(
                    [
                        'errors' => [
                            [
                                'status' => Response::HTTP_BAD_REQUEST,
                                'title' => $translator->trans('You have to wait …'),
                                'detail' => $translator->trans('This door cannot be opened until %do_not_open_until%', [
                                    '%do_not_open_until%' => $scene->getDoNotOpenUntil()->format(DateTimeImmutable::ATOM),
                                ]),
                            ],
                        ],
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        if (null === $scene->getOpenedAt()) {
            $scene->setOpenedAt($now);
            $entityManager->persist($scene);
            $entityManager->flush();
        }

        return $this->scenes($calendar, $serializer);
    }

    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $url = parent::generateUrl($route, $parameters, $referenceType);

        // Unescape placeholders (`{{ value }}`) in url.
        $startDelimiter = preg_quote(rawurlencode('{{'), '/');
        $endDelimiter = preg_quote(rawurlencode('}}'), '/');
        $pattern = '/'
            .$startDelimiter
            .'.+?' // Non-greedy match
            .'(?='.$endDelimiter.')' // With positive lookahead
            .$endDelimiter
            .'/';
        $url = preg_replace_callback(
            $pattern,
            static fn ($match) => rawurldecode($match['0']),
            $url
        );

        return $url;
    }

    private function renderScenes(iterable $scenes): iterable
    {
        foreach ($scenes as $scene) {
            $scene->setContent($this->renderContent($scene));
        }

        return $scenes;
    }

    private function renderContent(Scene $scene): string
    {
        $content = $scene->getContent();

        $imagesBaseUrl = $this->getParameter('app.images.base_url');
        if (null !== $scene->getContentImage() && null !== $scene->getContentImage()->getName()) {
            $imageUrl = $imagesBaseUrl.$scene->getContentImage()->getName();
            $pattern = '@\{{2}\s*contentImage\s*\}{2}@';
            $content = preg_replace($pattern, $imageUrl, $content);
        }

        // YouTube videos.
        $content = preg_replace(
            '@https://youtu.be/(?P<id>\S+)@',
            '<iframe width="560" height="315" src="https://www.youtube.com/embed/\1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
            $content
        );

        $content = preg_replace(
            '@https://www.youtube.com/watch\?v=(?P<id>\S+)@',
            '<iframe width="560" height="315" src="https://www.youtube.com/embed/\1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
            $content
        );

        // Image toggle.
        $content = preg_replace_callback(
            '@\[image-toggle\s+(\S+)\s+(\S+)\]@',
            static function ($matches) {
                $id = sha1(uniqid('', true));
                $imageUrls = [$matches[1], $matches[2]];

                return <<<HTML
<img src="{$imageUrls[0]}" id="{$id}-1" onclick="document.getElementById('{$id}-1').style.display = 'none'; document.getElementById('{$id}-2').style.display = 'initial'"/>
<img src="{$imageUrls[1]}" id="{$id}-2" style="display: none" onclick="document.getElementById('{$id}-2').style.display = 'none'; document.getElementById('{$id}-1').style.display = 'initial'"/>
HTML;
            },
            $content,
        );

        // Images.
        $content = preg_replace(
            '@(?P<url>\S+\.(jpg|png|jfif))@',
            '<img src="\0"/>',
            $content
        );

        // Bare urls.
        $content = preg_replace(
            '@(?<!["a-z])([a-z]+://\S+)@',
            '<a href="\1">\1</a>',
            $content
        );

        return $content;
    }
}
