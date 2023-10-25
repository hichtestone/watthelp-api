<?php

declare(strict_types=1);

namespace App\Controller\Budget\DeliveryPoint;

use App\Annotation\ConstraintValidator;
use App\Entity\Budget;
use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\Permission;
use App\Manager\Budget\DeliveryPointBudgetManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DeleteController
{
    private DeliveryPointBudgetManager $deliveryPointBudgetManager;

    public function __construct(DeliveryPointBudgetManager $deliveryPointBudgetManager)
    {
        $this->deliveryPointBudgetManager = $deliveryPointBudgetManager;
    }

    /**
     * @Route("/budget/{budget_id}/delivery-point-budget", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Budget\DeliveryPoint\DeleteConstraintList", options={"type"=Budget::class})
     * @Entity("budget", expr="repository.find(budget_id)")
     * @IsGranted("BELONG_CLIENT", subject="budget")
     * @IsGranted(Permission::BUDGET_DELETE)
     * 
     * @SWG\Response(response=204, description="No Content")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * 
     * @SWG\Tag(name="Budget")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     */
    public function __invoke(Request $request, Budget $budget): JsonResponse
    {
        $filters = $request->request->all();
        $filters['budget'] = $budget;

        $this->deliveryPointBudgetManager->deleteByFilters($filters);

        return new JsonResponse(null, 204);
    }
}