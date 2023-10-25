<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\User;
use App\Manager\UserManager;
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
    private UserManager $userManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        UserManager $userManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->userManager = $userManager;
    }

    /**
     * @Route("/user", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\User\PostConstraintList")
     * @IsGranted(Permission::USER_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a user",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="email", type="string", description="required"),
     *         @SWG\Property(property="password", type="string", description="required"),
     *         @SWG\Property(property="first_name", type="string", description="required"),
     *         @SWG\Property(property="last_name", type="string", description="required"),
     *         @SWG\Property(property="mobile", type="string", description=""),
     *         @SWG\Property(property="phone", type="string", description=""),
     *         @SWG\Property(property="avatar", type="integer", description="id of a file previously uploaded")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\User::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="User")
     * 
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();
        $data['language'] ??= $connectedUser->getClient()->getDefaultLanguage();

        $user = $this->serializer->denormalize($data, User::class);
        $user->setClient($connectedUser->getClient());

        $this->userManager->insert($user);

        return $this->responseHandler->handle($user, [], 201);
    }
}
