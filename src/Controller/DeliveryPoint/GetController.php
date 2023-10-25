<?php

declare(strict_types=1);

namespace App\Controller\DeliveryPoint;

use App\Entity\DeliveryPoint;
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
     * @Route("/delivery-point/{id}", methods={"GET"}, requirements={"id"="\d+"})
     * @Entity("deliverypoint", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="deliveryPoint")
     * @IsGranted(Permission::DELIVERY_POINT_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_contract,delivery_point_photo,delivery_point_delivery_point_invoices"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\DeliveryPoint::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="DeliveryPoint")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, DeliveryPoint $deliveryPoint): JsonResponse
    {
        return $this->responseHandler->handle($deliveryPoint);
    }
}
