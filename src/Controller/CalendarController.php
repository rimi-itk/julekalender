<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Scene;
use App\Security\Voter\CalendarVoter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @Route(priority=-10)
 */
class CalendarController extends AbstractController
{
    /** @var UploaderHelper */
    private $uploaderHelper;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * @Route("/{slug}", name="calendar_show")
     */
    public function show(Calendar $calendar): Response
    {
        $config = [
            'data_url' => $this->generateUrl('calendar_scenes', ['slug' => $calendar->getSlug()]),
            'scene_open_url_template' => $this->generateUrl('calendar_scenes_open', ['calendar' => $calendar->getId(), 'scene' => '{{ id }}']),
        ];

        if ($calendar->getAudio()) {
            $config['audio_url'] = $this->uploaderHelper->asset($calendar, 'audioFile');
            $config['audio_loop'] = $calendar->getAudioLoop();
        }

        return $this->render('calendar/show.html.twig', [
            'calendar' => $calendar,
            'config' => $config,
        ]);
    }

    /**
     * @Route("/{slug}/styles", name="calendar_styles")
     */
    public function styles(Calendar $calendar): Response
    {
        $response = new Response();
        $response->headers->set('content-type', 'text/css');

        return $this->render('calendar/styles.css.twig', [
            'calendar' => $calendar,
        ], $response);
    }

    /**
     * @Route("/{slug}/scenes", name="calendar_scenes")
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
        if (!$this->isGranted(CalendarVoter::EDIT, $calendar)
            && $scene->getDoNotOpenUntil() && $now < $scene->getDoNotOpenUntil()) {
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

        if (null !== $scene->getContentImage()) {
            $imageUrl = $this->uploaderHelper->asset($scene, 'contentImageFile');
            if (null !== $imageUrl) {
                $pattern = '@\{{2}\s*contentImage\s*\}{2}@';
                $content = preg_replace($pattern, $imageUrl, $content);
            }
        }

        // [reveal url url]
        $content = preg_replace_callback(
            '@\[reveal\s+(\S+)\s+(\S+)\]@',
            static function ($matches) {
                return <<<HTML
<div class="reveal"><div onclick="this.parentNode.firstChild.style.display = 'none'; this.parentNode.lastChild.style.display = 'initial'">${matches[1]}</div><div style="display: none">${matches[2]}</div></div>
HTML;
            },
            $content,
        );

        // [image-toggle url url]
        $content = preg_replace_callback(
            '@\[image-toggle\s+(\S+)\s+(\S+)\]@',
            static function ($matches) {
                $id = sha1(uniqid('', true));
                $imageUrls = [$matches[1], $matches[2]];

                return <<<HTML
<div class="toggle"><div onclick="this.parentNode.firstChild.style.display = 'none'; this.parentNode.lastChild.style.display = 'initial'">${matches[1]}</div><div onclick="this.parentNode.firstChild.style.display = 'initial'; this.parentNode.lastChild.style.display = 'none'" style="display: none">${matches[2]}</div></div>
HTML;
            },
            $content,
        );

        // YouTube videos.
        $content = preg_replace(
            '@https://youtu.be/(?P<id>[A-Za-z0-9_]+)@',
            '<iframe width="560" height="315" src="https://www.youtube.com/embed/\1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
            $content
        );

        $content = preg_replace(
            '@https://www.youtube.com/watch\?v=(?P<id>[A-Za-z0-9_]+)@',
            '<iframe width="560" height="315" src="https://www.youtube.com/embed/\1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
            $content
        );

        // Images.
        $content = preg_replace(
            // '@(?<!["a-z])([a-z]+://[A-Za-z0-9._~:/?#\[\]\@!$&\'()*+,;%=-]+)@',
            '@(?P<url>[A-Za-z0-9._~:/?#\[\]\@!$&\'()*+,;%=-]+\.(jpg|png|jfif))@',
            '<img src="\0"/>',
            $content
        );

        // Bare urls.
        $content = preg_replace(
            '@(?<!["a-z])([a-z]+://[A-Za-z0-9._~:/?#\[\]\@!$&\'()*+,;%=-]+)@',
            '<a href="\1">\1</a>',
            $content
        );

        return $content;
    }
}
