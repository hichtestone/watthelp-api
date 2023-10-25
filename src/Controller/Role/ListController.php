<?php

declare(strict_types=1);

namespace App\Controller\Role;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\RoleManager;
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
    private RoleManager $roleManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        RoleManager $roleManager,
        ResponseHandler $responseHandler
    ) {
        $this->roleManager = $roleManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/role", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Role\FilterConstraintList", options={"type"="App\Entity\Role"})
     * @IsGranted(Permission::ROLE_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"role_users","role_permissions","role_permission_codes"})
     * 
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","name","description","created_at","updated_at"})
     * @SWG\Parameter(name="filters[ids]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[exclude_ids]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[users]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[permissions]", in="query", type="array", @SWG\Items(type="string"))
     * @SWG\Parameter(name="filters[name]", in="query", type="string")
     * @SWG\Parameter(name="filters[description]", in="query", type="string")
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Role::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Role")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->all('filters') : [];

        $roles = $this->roleManager->findByFilters($connectedUser->getClient(), $filters, $pagination);

        return $this->responseHandler->handle($roles);
    }
}