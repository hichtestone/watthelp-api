<?php

declare(strict_types=1);

namespace App\Controller\Role;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\Role;
use App\Manager\RoleManager;
use App\Query\Criteria;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PutController
{
    private ResponseHandler $responseHandler;
    private RoleManager $roleManager;
    private SerializerInterface $serializer;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        RoleManager $roleManager
    ) {
        $this->responseHandler = $responseHandler;
        $this->roleManager = $roleManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/role/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @Entity("role", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\Role\RoleConstraintList")
     * @IsGranted("BELONG_CLIENT", subject="role")
     * @IsGranted(Permission::ROLE_EDIT)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"role_users","role_permissions","role_permission_codes"})
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Updates a role",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="name", type="string", description="required"),
     *         @SWG\Property(property="description", type="string", description=""),
     *         @SWG\Property(property="permissions", type="array", @SWG\Items(type="string")),
     *         @SWG\Property(property="users", type="array", @SWG\Items(type="integer"))
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Role::class, groups={"default"})),
     *     description="Returns the entity updated."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Role")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, Role $role) : JsonResponse
    {
        $put = $request->request->all();

        $role = $this->serializer->denormalize($put, Role::class, null, ['object_to_populate' => $role]);

        $this->roleManager->update($role);

        return $this->responseHandler->handle($role);
    }
}