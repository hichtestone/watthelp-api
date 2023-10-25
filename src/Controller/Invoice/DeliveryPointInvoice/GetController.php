<?php

declare(strict_types=1);

namespace App\Controller\Invoice\DeliveryPointInvoice;

use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Permission;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private ResponseHandler $responseHandler;

    public function __construct(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/delivery-point-invoice/{id}", methods={"GET"})
     * @Entity("deliverypointinvoice", expr="repository.find(id)")
     * @IsGranted(Permission::INVOICE_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_invoice_delivery_point,delivery_point_invoice_invoice,delivery_point_invoice_invoice_consumption,delivery_point_invoice_invoice_subscription,delivery_point_invoice_invoice_taxes"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=DeliveryPointInvoice::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="DeliveryPointInvoice")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, DeliveryPointInvoice $deliveryPointInvoice): JsonResponse
    {
        return $this->responseHandler->handle($deliveryPointInvoice);
    }
}