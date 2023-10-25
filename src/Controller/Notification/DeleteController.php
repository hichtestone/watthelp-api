<?php

declare(strict_types=1);

namespace App\Controller\Notification;

use App\Query\Criteria;
use App\Annotation\ConstraintValidator;
use App\Entity\Notification;
use App\Manager\NotificationManager;
use Doctrine\ORM\ORMException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DeleteController
{
    private NotificationManager $notificationManager;

    public function __construct(
        NotificationManager $notificationManager
    )
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * @Route("/notification/delete", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\DeleteMultipleConstraintList", options={"type"=Notification::class,"criteria"=Criteria\Notification\Id::class,"belongUser"=true})
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Deletes notifications",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="ids", type="array", @SWG\Items(type="integer"), description="required - can be either an array of notification ids OR *")
     *     )
     * )
     * 
     * @SWG\Response(response=204, description="No Content")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Notification")
     *
     * @param Request $request
     * @param UserInterface $connectedUser
     * @return JsonResponse
     * @throws ORMException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $filters = $data ?? [];
        $filters['user'] = $connectedUser;

        $this->notificationManager->deleteByFilters($filters);

        return new JsonResponse(null, 204);
    }
}
