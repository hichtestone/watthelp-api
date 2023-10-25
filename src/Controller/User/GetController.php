<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Permission;
use App\Entity\User;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private ResponseHandler $responseHandler;

    public function __construct(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/user/{id}", methods={"GET"})
     * @Entity("user", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="user")
     * @IsGranted("SAME_USER_OR_HAS_VIEW_PERMISSION", subject="user")
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"user_avatar"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\User::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="User")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, User $user): JsonResponse
    {
        return $this->responseHandler->handle($user);
    }
}
