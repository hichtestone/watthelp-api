<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Permission;
use App\Entity\User;
use App\Manager\UserManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteController
{
    private UserManager $userManager;
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        UserManager $userManager
    ) {
        $this->userManager = $userManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("user", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="user")
     * @IsGranted(Permission::USER_DELETE)
     *
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $this->userManager->delete($user);

        return new JsonResponse(null, 204);
    }
}
