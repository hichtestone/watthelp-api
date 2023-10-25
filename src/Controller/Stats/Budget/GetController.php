<?php

declare(strict_types=1);

namespace App\Controller\Stats\Budget;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\BudgetManager;
use App\Query\Criteria;
use App\Response\ResponseHandler;
use App\Service\BudgetService;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private BudgetManager $budgetManager;
    private BudgetService $budgetService;
    private ResponseHandler $responseHandler;

    public function __construct(
        BudgetManager $budgetManager,
        BudgetService $budgetService,
        ResponseHandler $responseHandler
    ) {
        $this->budgetManager = $budgetManager;
        $this->budgetService = $budgetService;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/stats/budget", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Stats\Budget\StatsConstraintList")
     * @IsGranted({Permission::DASHBOARD_VIEW, Permission::BUDGET_VIEW})
     *
     * @SWG\Response(
     *     response=200,
     *     description="budget statistics"
     * )
     *
     * @SWG\Tag(name="Stats")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $year = intval($request->query->get('year'));
        $client = $connectedUser->getClient();

        $budget = $this->budgetManager->getByCriteria($client, [new Criteria\Budget\Year($year)]);

        $expectedBudget = $this->budgetService->getExpectedBudgetByMonth($budget->getTotalConsumption(), $budget->getTotalAmount(), $budget->getAveragePrice());
        $consumedBudget = $this->budgetService->getConsumedBudgetByMonth($budget->getTotalAmount(), $budget->getAveragePrice(), $year, $client);
        $forecastBudget = $this->budgetService->getForecastBudgetByMonth($consumedBudget, $budget->getTotalAmount());

        $result = [
            'budget_amount' => $budget->getTotalAmount(),
            'expected' => $expectedBudget->getValues(),
            'consumed' => $consumedBudget->getBudgetByMonth()->getValues(),
            'forecast' => $forecastBudget->getValues()
        ];

        return $this->responseHandler->handle($result);
    }
}