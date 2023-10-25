<?php

declare(strict_types=1);

namespace App\Controller\Budget\Import;

use App\Annotation\ConstraintValidator;
use App\Entity\Import;
use App\Entity\Permission;
use App\Exceptions\UploadFailedException;
use App\Manager\ImportManager;
use App\Message\ImportMessage;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private ImportManager $importManager;
    private SerializerInterface $serializer;
    private MessageBusInterface $messageBus;

    public function __construct(
        ResponseHandler $responseHandler,
        ImportManager $importManager,
        SerializerInterface $serializer,
        MessageBusInterface $messageBus
    ) {
        $this->responseHandler = $responseHandler;
        $this->importManager = $importManager;
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/budget/import", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Import\ImportConstraintList")
     * @IsGranted(Permission::IMPORT_BUDGET)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Imports delivery points",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="file", type="integer", description="required")
     *     )
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Budget")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $budgetImport = $this->serializer->denormalize($data, Import::class);
        $budgetImport->setUser($connectedUser);
        $budgetImport->setType(Import::TYPE_BUDGET);

        $this->importManager->insert($budgetImport);

        $this->messageBus->dispatch(new ImportMessage($budgetImport->getId()));

        return $this->responseHandler->handle($budgetImport, [], 201);
    }
}