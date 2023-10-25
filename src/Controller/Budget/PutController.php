<?php

declare(strict_types=1);

namespace App\Controller\Budget;

use App\Annotation\ConstraintValidator;
use App\Entity\Budget;
use App\Entity\Permission;
use App\Manager\BudgetManager;
use App\Response\ResponseHandler;
use App\Serializer\Denormalizer\DefaultDenormalizer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PutController
{
    private ResponseHandler $responseHandler;
    private BudgetManager $budgetManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        BudgetManager $budgetManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->budgetManager = $budgetManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/budget/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @Entity("budget", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\Budget\BudgetPutConstraintList", options={"type"="App\Entity\Budget"})
     * @IsGranted("BELONG_CLIENT", subject="budget")
     * @IsGranted(Permission::BUDGET_EDIT)
     *
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"budget_delivery_point_budgets"})
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Updates a budget",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
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
     *     description="Returns the entity updated."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Budget")
     *
     * @return JsonResponse
     * @throws \Exception
     *
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Budget $budget, UserInterface $connectedUser): JsonResponse
    {
        $put = $request->request->all();

        $budget = $this->serializer->denormalize($put, DefaultDenormalizer::class, null, ['object_to_populate' => $budget]);

        $this->budgetManager->update($budget);

        return $this->responseHandler->handle($budget);
    }
}