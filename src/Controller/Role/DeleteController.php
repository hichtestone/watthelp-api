<?php

declare(strict_types=1);

namespace App\Controller\Role;

use App\Entity\Permission;
use App\Entity\Role;
use App\Manager\RoleManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DeleteController
{
    private RoleManager $roleManager;

    public function __construct(RoleManager $roleManager) {
        $this->roleManager = $roleManager;
    }

    /**
     * @Route("/role/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("role", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="role")
     * @IsGranted(Permission::ROLE_DELETE)
     *
     * @SWG\Response(response=204, description="No Content")
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Role")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     */
    public function __invoke(Request $request, Role $role): JsonResponse
    {
        $this->roleManager->delete($role);

        return new JsonResponse(null, 204);
    }
}