<?php

declare(strict_types=1);

namespace App\Controller\Contract;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\ContractManager;
use App\Request\Pagination;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ListController
{
    private ContractManager $contractManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        ContractManager $contractManager,
        ResponseHandler $responseHandler
    )
    {
        $this->contractManager = $contractManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/contract", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Contract\FilterConstraintList", options={"type"="App\Entity\Contract"})
     * @IsGranted(Permission::CONTRACT_VIEW)
     *
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","reference", "started_at", "finished_at", "provider", "type"})
     * @SWG\Parameter(name="filters[id]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[exclude_ids]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[reference]", in="query", type="string")
     * @SWG\Parameter(name="filters[provider]", in="query", type="string")
     * @SWG\Parameter(name="filters[type]", in="query", type="string")
     * @SWG\Parameter(name="filters[invoice_period]", in="query", type="string")
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"contract_pricings"})
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Contract::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Contract")
     *
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->all('filters') : [];

        $data = $this->contractManager->findByFilters($connectedUser->getClient(), $filters, $pagination);

        return $this->responseHandler->handle($data);
    }
}
