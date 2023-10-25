<?php

declare(strict_types=1);

namespace App\Controller\Notification;

use App\Annotation\ConstraintValidator;
use App\Manager\NotificationManager;
use App\Request\Pagination;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ListController
{
    private NotificationManager $notificationManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        NotificationManager $notificationManager,
        ResponseHandler $responseHandler
    )
    {
        $this->notificationManager = $notificationManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/notification", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Notification\FilterConstraintList", options={"type"="App\Entity\Notification"})
     *
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","message","url", "status", "created_at"})
     * @SWG\Parameter(name="filters[ids]", in="query", type="array", @SWG\Items(type="integer"))
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Notification::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Notification")
     *
     * @param Request $request
     * @param Pagination $pagination
     * @param UserInterface $connectedUser
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->get('filters', []);
        $filters['user'] = $connectedUser;

        $notifications = $this->notificationManager->findByFilters($filters, $pagination);
        return $this->responseHandler->handle($notifications, ['X-Mercure-Uri' => sha1(strval($connectedUser->getId()))]);
    }
}
