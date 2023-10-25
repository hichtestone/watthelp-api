<?php

declare(strict_types=1);

namespace App\Controller\Pricing;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\PricingManager;
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
    private PricingManager $pricingManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        PricingManager $pricingManager,
        ResponseHandler $responseHandler
    ) {
        $this->pricingManager = $pricingManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/pricing", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Pricing\FilterConstraintList", options={"type"="App\Entity\Pricing"})
     * @IsGranted(Permission::PRICING_VIEW)
     *
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","first_name","last_name","email","phone","mobile","enabled"})
     *
     * @SWG\Parameter(name="filters[id]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[exclude_ids]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[first_name]", in="query", type="string")
     * @SWG\Parameter(name="filters[last_name]", in="query", type="string")
     * @SWG\Parameter(name="filters[phone]", in="query", type="string")
     * @SWG\Parameter(name="filters[mobile]", in="query", type="string")
     * @SWG\Parameter(name="filters[email]", in="query", type="string")
     * @SWG\Parameter(name="filters[enabled]", in="query", type="boolean")
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Pricing::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Pricing")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->all('filters') : [];

        $data = $this->pricingManager->findByFilters($connectedUser->getClient(), $filters, $pagination);

        return $this->responseHandler->handle($data);
    }
}