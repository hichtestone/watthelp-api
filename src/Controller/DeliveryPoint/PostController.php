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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private SerializerInterface $serializer;
    private DeliveryPointManager $deliveryPointManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        DeliveryPointManager $deliveryPointManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->deliveryPointManager = $deliveryPointManager;
    }

    /**
     * @Route("/delivery-point", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\DeliveryPoint\DeliveryPointConstraintList")
     * @IsGranted(Permission::DELIVERY_POINT_EDIT)
     *  
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_contract,delivery_point_photo,delivery_point_delivery_point_invoices"})
     * 
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a delivery point",
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
     *     description="Returns the entity created."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="DeliveryPoint")
     * 
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $deliveryPoint = $this->serializer->denormalize($data, DeliveryPoint::class);
        $deliveryPoint->setClient($connectedUser->getClient());

        $this->deliveryPointManager->insert($deliveryPoint);

        return $this->responseHandler->handle($deliveryPoint, [], 201);
    }
}