<?php

declare(strict_types=1);

namespace App\Controller\DeliveryPoint;

use App\Entity\DeliveryPoint;
use App\Entity\Permission;
use App\Manager\DeliveryPointManager;
use Nelmio\ApiDocBundle\Annotation\Model;
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
    private DeliveryPointManager $deliveryPointManager;
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        DeliveryPointManager $deliveryPointManager
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/delivery-point/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("deliverypoint", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="deliveryPoint")
     * @IsGranted(Permission::DELIVERY_POINT_DELETE)
     * 
     * @SWG\Response(response=204, description="No Content")
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="DeliveryPoint")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, DeliveryPoint $deliveryPoint): JsonResponse
    {
        $this->deliveryPointManager->delete($deliveryPoint);

        return new JsonResponse(null, 204);
    }
}