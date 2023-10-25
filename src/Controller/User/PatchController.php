<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Annotation\ConstraintValidator;
use App\Controller\AbstractPatchController;
use App\Entity\Permission;
use App\Entity\User;
use App\Manager\FileManager;
use App\Manager\UserManager;
use App\Query\Criteria;
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

class PatchController extends AbstractPatchController
{
    private User $user;
    private UserManager $userManager;
    private ResponseHandler $responseHandler;
    private FileManager $fileManager;

    public function __construct(
        UserManager $userManager,
        FileManager $fileManager,
        ResponseHandler $responseHandler
    ) {
        $this->userManager = $userManager;
        $this->responseHandler = $responseHandler;
        $this->fileManager = $fileManager;
    }

    /**
     * @Route("/user/{id}", methods={"PATCH"})
     * @Entity("user", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\User\OperationConstraintList", options={"type"=User::class})
     * @IsGranted("SAME_USER_OR_HAS_EDIT_PERMISSION", subject="user")
     *
     * @SWG\Parameter(
     *     name="operations",
     *     in="body",
     *     description="Updates a property of User",
     *     required=true,
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(
     *          type="object",
     *          @SWG\Property(property="op", type="string", example={"replace"}),
     *          @SWG\Property(property="path", type="string", example={"/email"}),
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
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="User")
     *
     * 
     * @throws \Exception
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, User $user, UserInterface $connectedUser): JsonResponse
    {
        $this->user = $user;

        $this->handlePatchRequest($request->request->all(), $user, [
            'email',
            'firstName',
            'lastName',
            'phone',
            'mobile',
            'avatar',
        ]);

        $this->userManager->update($user);

        return $this->responseHandler->handle($user);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function replaceAvatar($value): void
    {
        $file = $this->fileManager->getByCriteria([new Criteria\File\Id($value)]);
        $file->setUser($this->user);

        $this->fileManager->update($file);

        $this->user->setAvatar($file);
    }
}
