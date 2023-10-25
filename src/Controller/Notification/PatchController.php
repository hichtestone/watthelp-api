<?php

declare(strict_types=1);

namespace App\Controller\Notification;

use App\Annotation\ConstraintValidator;
use App\Entity\Notification;
use App\Manager\NotificationManager;
use App\Query\Criteria;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatchController
{
    private NotificationManager $notificationManager;
    private ResponseHandler $responseHandler;
    private SerializerInterface $serializer;
    private TranslatorInterface $translator;


    public function __construct(
        NotificationManager $notificationManager,
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        TranslatorInterface $translator
    )
    {
        $this->notificationManager = $notificationManager;
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * @Route("/notification/{id}", methods={"PATCH"})
     * @ConstraintValidator(class="App\Request\Validator\Notification\OperationConstraintList")
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Notification::class, groups={"default"})),
     *     description="Returns the entity updated."
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Notification")
     *
     *
     * @param Request $request
     * @param UserInterface $connectedUser
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $id = $request->attributes->get('id');

        if ('*' !== $id) {
            /** @var Notification $notification */
            $notification = $this->notificationManager->getByCriteria([
                new Criteria\Notification\Id($id),
            ]);
            if (!$notification) {
                throw new NotFoundHttpException($this->translator->trans('Notification "{{ id }}" cannot be found', ['{{ id }}' => $id]));
            }
        }

        $data = $this->serializer->decode($request->getContent(), 'json');

        foreach ($data['operations'] as $idx => $op) {
            if ('/read' === $op['path'] && 'replace' === $op['op']) {
                if ('*' === $id) {
                    $this->notificationManager->markAllAsRead($connectedUser);
                } else {
                    $notification->setIsRead(boolval($op['value']));
                    $this->notificationManager->update($notification);
                }
            }
        }

        return $this->responseHandler->handle();
    }
}
