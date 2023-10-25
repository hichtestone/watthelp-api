<?php

declare(strict_types=1);

namespace App\Controller\Budget;

use App\Annotation\ConstraintValidator;
use App\Entity\Budget;
use App\Entity\Permission;
use App\Manager\BudgetManager;
use App\Query\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DeleteController
{
    private BudgetManager $budgetManager;

    public function __construct(BudgetManager $budgetManager) {
        $this->budgetManager = $budgetManager;
    }

    /**
     * The method is POST because the front can't handle a DELETE call with a body
     * @Route("/budget/delete", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\DeleteMultipleConstraintList", options={"type"=Budget::class,"criteria"=Criteria\Budget\Id::class})
     * @IsGranted(Permission::BUDGET_DELETE)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Deletes budgets",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="ids", type="array", @SWG\Items(type="integer"), description="required - can be either an array of budget ids OR *")
     *     )
     * )
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
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->request->all();

        $this->budgetManager->deleteByFilters($connectedUser->getClient(), $filters);

        return new JsonResponse(null, 204);
    }
}