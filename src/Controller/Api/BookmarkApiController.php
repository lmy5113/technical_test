<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Bookmark;
use App\Entity\ImageBookmark;
use App\Entity\VideoBookmark;
use App\Repository\BookmarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Embed\Embed;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * bookmark api controller for create/get/delete
 * @todo API token system
 */
final class BookmarkApiController extends AbstractController
{
    public function __construct(
        protected BookmarkRepository $bookmarkRepository,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * create bookmark into DB by infos from url
     * success : 204 no content
     * failed : 400 bad request
     * @Route("/bookmarks", name="create_bookmark", methods="POST")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function createBookmark(Request $request, EntityManagerInterface $em): Response
    {
        try {
            //check mandatory data first
            $response = new Response();
            if (!$request->request->has("url")) {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST)
                    ->setContent("mandatory parameter url missing");

                return $response;
            }

            $url = $request->request->get("url");
            //check url is valid
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $response->setStatusCode(Response::HTTP_BAD_REQUEST)
                    ->setContent("invalidate parameter url");

                return $response;
            }

            //get all infos from url
            $embed = new Embed();
            $info = $embed->get($url);
            $oembed = $info->getOEmbed();
            $providerName = $info->providerName;
            $bookmark = new Bookmark();
            switch ($providerName) {
                case 'Flickr':
                    $bookmark = new ImageBookmark();
                    $bookmark->setWidth(\intval($oembed->get('height')))
                        ->setHeight(\intval($oembed->get('width')));
                    break;
                case 'Vimeo':
                    $bookmark = new VideoBookmark();
                    $bookmark->setWidth(\intval($oembed->get('width')))
                        ->setHeight(\intval($oembed->get('height')))
                        ->setDuration(\intval($oembed->get('duration')));
                    break;
                //@todo add more providers
                default:
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST)
                        ->setContent("Provider {$providerName} not supported yet");
                    return $response;
            }
            //general setting for all bookmarks
            $bookmark->setUrl($url)
                ->setProvider($providerName)
                ->setTitle($info->title)
                ->setAuthor($info->authorName)
                ->setPublicationDate($info->publishedTime);

            $em->persist($bookmark);
            $em->flush();

            $response->setStatusCode(Response::HTTP_NO_CONTENT);
            return $response;
        } catch (Exception $e) {
            //@todo maybe save error log into DB
            $this->logger->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
                ->setContent("Internal server error");

            return $response;
        }
    }


    /**
     * return list of all bookmarks
     * success : 200 ok
     * failed : 500 internal server error
     * @Route("/bookmarks", name="list_bookmarks", methods="GET", priority="1")
     * @return Response
     */
    public function listAllBookmarks(SerializerInterface $serializer): Response
    {
        try {
            $bookmarks = $this->bookmarkRepository->findAll();
            $response = JsonResponse::fromJsonString($serializer->serialize($bookmarks, 'json'));
            $response->setStatusCode(Response::HTTP_OK);

            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
                ->setContent("Internal server error");

            return $response;
        }
    }

    /**
     * delete bookmark by id
     * success: 204 no content
     * failed: 404 not found
     * @Route("/bookmarks/{id}", name="delete_bookmark", methods="DELETE", requirements={"id"="\d+"})
     * @param integer $id
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function deleteBookmark(int $id, EntityManagerInterface $em): Response
    {
        try {
            $bookmark = $this->bookmarkRepository->find($id);
            $response = new Response();
            if (is_null($bookmark)) {
                $response->setStatusCode(Response::HTTP_NOT_FOUND)
                    ->setContent("no bookmark found for {$id}");

                return $response;
            }

            $em->remove($bookmark);
            $em->flush();

            $response->setStatusCode(Response::HTTP_NO_CONTENT)
                ->setContent("Bookmark {$id} is deleted");

            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
                ->setContent("Internal server error");

            return $response;
        }
    }
}
