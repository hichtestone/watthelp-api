<?php

declare(strict_types=1);

namespace App\Controller\Role;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\Role;
use App\Manager\RoleManager;
use App\Response\ResponseHandler;
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
    private RoleManager $roleManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        RoleManager $roleManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->roleManager = $roleManager;
    }

    /**
     * @Route("/role", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Role\RoleConstraintList")
     * @IsGranted(Permission::ROLE_EDIT)
     *
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"role_users","role_permissions","role_permission_codes"})
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a role",
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
     *     description="Returns the entity created."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Role")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $role = $this->serializer->denormalize($data, Role::class);
        $role->setClient($connectedUser->getClient());

        $this->roleManager->insert($role);

        return $this->responseHandler->handle($role, [], 201);
    }
}