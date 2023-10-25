<?php

declare(strict_types=1);

namespace App\Controller\Permission;

use App\Annotation\ConstraintValidator;
use App\Manager\PermissionManager;
use App\Request\Pagination;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Swagger\Annotations as SWG;


class ListController
{
    private PermissionManager $permissionManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        PermissionManager $permissionManager,
        ResponseHandler $responseHandler
    ) {
        $this->permissionManager = $permissionManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/permission", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Permission\FilterConstraintList", options={"type"="App\Entity\Permission"})
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"role_users","role_permissions","role_permission_codes"})
     * 
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id"})
     * @SWG\Parameter(name="filters[ids]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[codes]", in="query", type="array", @SWG\Items(type="string"))
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Permission::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Permission")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->all('filters') : [];

        $permissions = $this->permissionManager->findByFilters($filters, $pagination);

        return $this->responseHandler->handle($permissions);
    }
}