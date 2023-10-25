<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\User;
use App\Manager\UserManager;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PutController
{
    private ResponseHandler $responseHandler;
    private UserManager $userManager;
    private SerializerInterface $serializer;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        UserManager $userManager
    ) {
        $this->responseHandler = $responseHandler;
        $this->userManager = $userManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @Entity("user", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\User\PutConstraintList", options={"type"="App\Entity\User"})
     * @IsGranted("BELONG_CLIENT", subject="user")
     * @IsGranted("SAME_USER_OR_HAS_EDIT_PERMISSION", subject="user")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Updates a user",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="email", type="string", description="required"),
     *         @SWG\Property(property="first_name", type="string", description="required"),
     *         @SWG\Property(property="last_name", type="string", description="required"),
     *         @SWG\Property(property="password", type="string", description=""),
     *         @SWG\Property(property="mobile", type="string", description=""),
     *         @SWG\Property(property="phone", type="string", description="")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\User::class, groups={"default"})),
     *     description="Returns the entity updated."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="User")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, User $user, UserInterface $connectedUser) : JsonResponse
    {
        $put = $request->request->all();

        $user = $this->serializer->denormalize($put, User::class, null, ['object_to_populate' => $user]);

        $this->userManager->update($user);

        return $this->responseHandler->handle($user);
    }
}
