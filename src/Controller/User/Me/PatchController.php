<?php

declare(strict_types=1);

namespace App\Controller\User\Me;

use App\Annotation\ConstraintValidator;
use App\Controller\AbstractPatchController;
use App\Manager\UserManager;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PatchController extends AbstractPatchController
{
    private UserManager $userManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        UserManager $userManager,
        ResponseHandler $responseHandler
    ) {
        $this->userManager = $userManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/user/me", methods={"PATCH"})
     * @ConstraintValidator(class="App\Request\Validator\User\Me\UserMeConstraintList")
     *
     * @SWG\Parameter(
     *     name="operations",
     *     in="body",
     *     description="Updates the dashboard of User",
     *     required=true,
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(
     *          type="object",
     *          @SWG\Property(property="op", type="string", example={"replace"}),
     *          @SWG\Property(property="path", type="string", example={"/dashboard"}),
     *          @SWG\Property(property="value", type="string")
     *      )
     *    )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\User::class, groups={"default"})),
     *     description="Returns the entity updated."
     * ) 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="User")
     *
     * @throws \Exception
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $this->handlePatchRequest($request->request->all(), $connectedUser, [
            'dashboard',
            'language'
        ]);

        $this->userManager->update($connectedUser);

        return $this->responseHandler->handle($connectedUser);
    }
}