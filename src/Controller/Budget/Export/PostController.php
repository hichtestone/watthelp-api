<?php

declare(strict_types=1);

namespace App\Controller\Budget\Export;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Message\ExportMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PostController
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/budget/export", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Budget\ExportConstraintList")
     * @IsGranted(Permission::EXPORT_BUDGET)
     *
     * @SWG\Parameter(name="filters[ids]", in="query", type="array", @SWG\Items(type="integer"))
     *
     * @SWG\Response(
     *     response=201,
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Budget")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $message = new ExportMessage(
            $connectedUser->getId(),
            ExportMessage::TYPE_BUDGET,
            $data['filters'] ?? [],
            ExportMessage::FORMAT_EXCEL,
        );

        $this->messageBus->dispatch($message);

        return new JsonResponse(null, 201);
    }
}