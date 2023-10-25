<?php

declare(strict_types=1);

namespace App\Controller\DeliveryPoint;

use App\Entity\Permission;
use App\Manager\DeliveryPointManager;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class MapController
{
    private DeliveryPointManager $deliveryPointManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        ResponseHandler $responseHandler
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/delivery-point/map", methods={"GET"})
     * @IsGranted(Permission::DELIVERY_POINT_MAP)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="simplified delivery point info"
     * )
     * 
     * @SWG\Tag(name="DeliveryPoint")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $this->deliveryPointManager->getMapInfo($connectedUser->getClient());

        return $this->responseHandler->handle($data);
    }
}