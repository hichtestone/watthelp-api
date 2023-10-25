<?php

declare(strict_types=1);

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Entity\Permission;
use App\Manager\InvoiceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteController
{
    private InvoiceManager $invoiceManager;
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        InvoiceManager $invoiceManager
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/invoice/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("invoice", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="invoice")
     * @IsGranted(Permission::INVOICE_DELETE)
     *
     * @SWG\Response(response=204, description="No Content")
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Invoice")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, Invoice $invoice): JsonResponse
    {
        $this->invoiceManager->delete($invoice);

        return new JsonResponse(null, 204);
    }
}