<?php

declare(strict_types=1);

namespace App\Controller\Budget\DeliveryPoint;

use App\Annotation\ConstraintValidator;
use App\Entity\Budget;
use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\Permission;
use App\Manager\Budget\DeliveryPointBudgetManager;
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
    private SerializerInterface $serializer;
    private DeliveryPointBudgetManager $deliveryPointBudgetManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        DeliveryPointBudgetManager $deliveryPointBudgetManager
    ) {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->deliveryPointBudgetManager = $deliveryPointBudgetManager;
    }

    /**
     * @Route("/budget/{budget_id}/delivery-point-budget/{delivery_point_budget_id}", methods={"PUT"})
     * @Entity("budget", expr="repository.find(budget_id)")
     * @Entity("deliveryPointBudget", expr="repository.find(delivery_point_budget_id)")
     * @ConstraintValidator(class="App\Request\Validator\Budget\DeliveryPoint\DeliveryPointBudgetConstraintList")
     * @IsGranted("BELONG_CLIENT", subject="budget")
     * @IsGranted("BELONG_BUDGET", subject={"deliveryPointBudget","budget"})
     * @IsGranted(Permission::BUDGET_EDIT)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_budget_budget","delivery_point_budget_delivery_point"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Budget\DeliveryPointBudget::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Budget")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Budget $budget, DeliveryPointBudget $deliveryPointBudget): JsonResponse
    {
        $put = $request->request->all();

        $deliveryPointBudget = $this->serializer->denormalize($put, DeliveryPointBudget::class, null, ['object_to_populate' => $deliveryPointBudget]);
        $deliveryPointBudget->setBudget($budget);

        $this->deliveryPointBudgetManager->update($deliveryPointBudget);

        return $this->responseHandler->handle($deliveryPointBudget);
    }
}