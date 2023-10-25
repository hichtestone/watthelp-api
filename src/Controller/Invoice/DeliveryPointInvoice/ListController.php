<?php

declare(strict_types=1);

namespace App\Controller\Invoice\DeliveryPointInvoice;

use App\Annotation\ConstraintValidator;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Permission;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Request\Pagination;
use App\Request\Validator\Invoice\DeliveryPointInvoice\FilterConstraintList;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class ListController
{
    private DeliveryPointInvoiceManager $deliveryPointInvoiceManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        DeliveryPointInvoiceManager $deliveryPointInvoiceManager,
        ResponseHandler $responseHandler
    ) {
        $this->deliveryPointInvoiceManager = $deliveryPointInvoiceManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/delivery-point-invoice", methods={"GET"})
     * @ConstraintValidator(class=FilterConstraintList::class)
     * @IsGranted({Permission::INVOICE_VIEW, Permission::DELIVERY_POINT_VIEW})
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_invoice_delivery_point,delivery_point_invoice_invoice,delivery_point_invoice_invoice_consumption,delivery_point_invoice_invoice_subscription, delivery_point_invoice_invoice_taxes,delivery_point_invoice_delivery_point_invoice_analysis"})
     * 
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","amount_ht","amount_tva","amount_ttc","emitted_at"})
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=DeliveryPointInvoice::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="DeliveryPointInvoice")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->all('filters') ?? [];
        // for now we only list the credit notes
        $filters['is_credit_note'] = true;

        $data = $this->deliveryPointInvoiceManager->findByFilters($connectedUser->getClient(), $filters, $pagination);

        return $this->responseHandler->handle($data);
    }
}
