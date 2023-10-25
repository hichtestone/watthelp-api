<?php

declare(strict_types=1);

namespace App\Controller\DeliveryPoint;

use App\Annotation\ConstraintValidator;
use App\Entity\DeliveryPoint;
use App\Entity\Permission;
use App\Manager\DeliveryPointManager;
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
use Symfony\Component\Serializer\SerializerInterface;

class PutController
{
    private ResponseHandler $responseHandler;
    private DeliveryPointManager $deliveryPointManager;
    private SerializerInterface $serializer;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        DeliveryPointManager $deliveryPointManager
    ) {
        $this->responseHandler = $responseHandler;
        $this->deliveryPointManager = $deliveryPointManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/delivery-point/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @Entity("deliverypoint", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\DeliveryPoint\DeliveryPointConstraintList", options={"type"="App\Entity\DeliveryPoint"})
     * @IsGranted("BELONG_CLIENT", subject="deliveryPoint")
     * @IsGranted(Permission::DELIVERY_POINT_EDIT)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_contract,delivery_point_photo,delivery_point_delivery_point_invoices"})
     * 
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Updates a delivery point",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="name", type="string", description="required"),
     *         @SWG\Property(property="reference", type="string", description="required - unique"),
     *         @SWG\Property(property="code", type="string", description="unique"),
     *         @SWG\Property(property="address", type="string", description="required"),
     *         @SWG\Property(property="latitude", type="string", description=""),
     *         @SWG\Property(property="longitude", type="string", description=""),
     *         @SWG\Property(property="meter_reference", type="string", description="required"),
     *         @SWG\Property(property="power", type="string", description="required"),
     *         @SWG\Property(property="contract", type="integer", description="required"),
     *         @SWG\Property(property="photo", type="integer", description=""),
     *         @SWG\Property(property="description", type="string", description="")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\DeliveryPoint::class, groups={"default"})),
     *     description="Returns the entity updated."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="DeliveryPoint")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, DeliveryPoint $deliveryPoint) : JsonResponse
    {
        $put = $request->request->all();

        $deliveryPoint = $this->serializer->denormalize($put, DeliveryPoint::class, null, ['object_to_populate' => $deliveryPoint]);

        $this->deliveryPointManager->update($deliveryPoint);

        return $this->responseHandler->handle($deliveryPoint);
    }
}