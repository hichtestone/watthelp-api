<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Pdf;

use App\Annotation\ConstraintValidator;
use App\Entity\Invoice;
use App\Entity\Permission;
use App\Manager\FileManager;
use App\Manager\InvoiceManager;
use App\Query\Criteria;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private InvoiceManager $invoiceManager;
    private FileManager $fileManager;

    public function __construct(
        ResponseHandler $responseHandler,
        InvoiceManager $invoiceManager,
        FileManager $fileManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->invoiceManager = $invoiceManager;
        $this->fileManager = $fileManager;
    }

    /**
     * @Route("/invoice/{id}/pdf", methods={"POST"})
     * @Entity("invoice", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\Invoice\Pdf\PdfConstraintList")
     * @IsGranted("BELONG_CLIENT", subject="invoice")
     * @IsGranted(Permission::INVOICE_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Adds or removes the pdf of the invoice",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="pdf", type="integer", description="file ID or null")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Invoice::class, groups={"default"})),
     *     description="Returns the invoice entity."
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Invoice")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(Request $request, Invoice $invoice): JsonResponse
    {
        $fileId = $request->request->get('pdf');

        $file = is_null($fileId) ? null : $this->fileManager->getByCriteria([new Criteria\File\Id($fileId)]);

        $invoice->setPdf($file);

        $this->invoiceManager->update($invoice);

        return $this->responseHandler->handle($invoice);
    }
}