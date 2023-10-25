<?php

declare(strict_types=1);

namespace App\Controller\Budget\DeliveryPoint;

use App\Annotation\ConstraintValidator;
use App\Entity\Budget;
use App\Entity\Permission;
use App\Manager\Budget\DeliveryPointBudgetManager;
use App\Request\Pagination;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class ListController
{
    private DeliveryPointBudgetManager $deliveryPointBudgetManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        DeliveryPointBudgetManager $deliveryPointBudgetManager,
        ResponseHandler $responseHandler
    ) {
        $this->deliveryPointBudgetManager = $deliveryPointBudgetManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/budget/delivery-point-budget", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Budget\DeliveryPoint\FilterConstraintList", options={"type"="App\Entity\Budget\DeliveryPointBudget"})
     * @IsGranted(Permission::BUDGET_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"delivery_point_budget_budget","delivery_point_budget_delivery_point"})
     * 
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id"})
     *
     * @SWG\Parameter(name="filters[budget]", in="query", type="integer")
     * @SWG\Parameter(name="filters[year]", in="query", type="string")
     * @SWG\Parameter(name="filters[delivery_point]", in="query", type="integer")
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Budget\DeliveryPointBudget::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Budget")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->get('filters') : [];

        $deliveryPointBudgets = $this->deliveryPointBudgetManager->findByFilters($filters, $pagination);

        return $this->responseHandler->handle($deliveryPointBudgets);
    }
}