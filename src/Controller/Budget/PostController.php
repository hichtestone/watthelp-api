<?php

declare(strict_types=1);

namespace App\Controller\Budget;

use App\Annotation\ConstraintValidator;
use App\Entity\Budget;
use App\Entity\Permission;
use App\Manager\BudgetManager;
use App\Response\ResponseHandler;
use App\Serializer\Denormalizer\DefaultDenormalizer;
use App\Service\BudgetService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private SerializerInterface $serializer;
    private BudgetManager $budgetManager;
    private BudgetService $budgetService;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        BudgetManager $budgetManager,
        BudgetService $budgetService
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->budgetManager = $budgetManager;
        $this->budgetService = $budgetService;
    }

    /**
     * @Route("/budget", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Budget\BudgetPostConstraintList", options={"type"="App\Entity\Budget"})
     * @IsGranted(Permission::BUDGET_EDIT)
     *
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"budget_delivery_point_budgets"})
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a budget",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="year", type="integer", description="required"),
     *         @SWG\Property(property="average_price", type="integer", description="required"),
     *         @SWG\Property(property="total_hours", type="integer", description="required"),
     *         @SWG\Property(property="total_consumption", type="integer", description="optional"),
     *         @SWG\Property(property="total_amount", type="integer", description="optional")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Budget::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Budget")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $budget = $this->serializer->denormalize($data, DefaultDenormalizer::class, null, ['object_to_populate' => new Budget()]);
        $budget->setClient($connectedUser->getClient());

        $previousBudget = $this->budgetManager->getPrevious($budget);
        if ($previousBudget) {
            $budget->setTotalHours($previousBudget->getTotalHours());
            $budget->setAveragePrice($previousBudget->getAveragePrice());
            $budget->setTotalConsumption($previousBudget->getTotalConsumption());
            $budget->setTotalAmount($previousBudget->getTotalAmount());
        }

        $budget = $this->budgetService->createDeliveryPointBudgets($budget, $previousBudget);

        $this->budgetManager->insert($budget);

        return $this->responseHandler->handle($budget, [], 201);
    }
}
