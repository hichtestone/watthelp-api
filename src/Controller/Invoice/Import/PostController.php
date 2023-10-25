<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Import;

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
     * @Route("/invoice/import", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Invoice\Import\ImportConstraintList")
     * @IsGranted(Permission::IMPORT_INVOICE)
     *
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=true, type="string", description="Expand data.")
     * @SWG\Parameter(
     *     name="file",
     *     in="formData",
     *     description="Upload invoice files",
     *     required=true,
     *     type="file"
     * )
     * @SWG\Response(
     *     response=201,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Import::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Tag(name="InvoiceImport")
     *
     * @param Request $request
     * @param UserInterface $connectedUser
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ExceptionInterface
     * @throws UploadFailedException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $invoiceImport = $this->serializer->denormalize($data, Import::class);
        $invoiceImport->setUser($connectedUser);
        $invoiceImport->setType(Import::TYPE_INVOICE);

        $this->importManager->insert($invoiceImport);

        $this->messageBus->dispatch(new ImportMessage($invoiceImport->getId(), $data['reimport_invoices'] ?? []));

        return $this->responseHandler->handle($invoiceImport, [], 201);
    }
}
