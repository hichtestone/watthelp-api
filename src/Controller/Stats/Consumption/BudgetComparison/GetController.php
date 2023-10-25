<?php

declare(strict_types=1);

namespace App\Controller\Stats\Consumption\BudgetComparison;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Factory\PeriodFactory;
use App\Manager\DeliveryPointManager;
use App\Manager\Invoice\InvoiceConsumptionManager;
use App\Response\ResponseHandler;
use App\Service\BudgetService;
use App\Service\ConsumptionService;
use App\Service\ConsumptionService\Context;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private DeliveryPointManager $deliveryPointManager;
    private InvoiceConsumptionManager $invoiceConsumptionManager;
    private ConsumptionService $consumptionService;
    private BudgetService $budgetService;
    private ResponseHandler $responseHandler;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        InvoiceConsumptionManager $invoiceConsumptionManager,
        ConsumptionService $consumptionService,
        BudgetService $budgetService,
        ResponseHandler $responseHandler
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->invoiceConsumptionManager = $invoiceConsumptionManager;
        $this->consumptionService = $consumptionService;
        $this->budgetService = $budgetService;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/stats/consumption/budget-comparison", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Stats\Consumption\BudgetComparison\StatsConstraintList")
     * @IsGranted({Permission::DASHBOARD_VIEW, Permission::INVOICE_VIEW, Permission::BUDGET_VIEW})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Compares the consumption with the budget"
     * )
     *
     * @SWG\Tag(name="Stats")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $params = $request->query->all();
        
        $period = PeriodFactory::createFromStrings($params['period']['start'], $params['period']['end']);
        if (!empty($params['delivery_point_filters'] ?? [])) {
            $deliveryPoints = $this->deliveryPointManager->findByFilters($connectedUser->getClient(), $params['delivery_point_filters']);
            $deliveryPoints = iterator_to_array($deliveryPoints);
        }
        $deliveryPoints ??= [];

        $actualConsumptions = $this->consumptionService->getTotalConsumptionByPeriod($connectedUser->getClient(), $period, $deliveryPoints);
        $budgetedConsumptions = $this->budgetService->getExpectedBudgetConsumptionByMonth($connectedUser->getClient(), $period, $deliveryPoints);

        $response = [
            'consumed' => array_map(fn (?int $consumption) => $consumption ? $consumption * 100 : null, $actualConsumptions->getValues()),
            'budgeted' => $budgetedConsumptions->getValues()
        ];

        return $this->responseHandler->handle($response);
    }
}