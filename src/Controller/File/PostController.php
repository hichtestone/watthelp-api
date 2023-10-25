<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Annotation\ConstraintValidator;
use App\Manager\FileManager;
use App\Response\ResponseHandler;
use App\Service\S3Uploader;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private FileManager $fileManager;
    private SerializerInterface $serializer;
    private S3Uploader $uploader;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        FileManager $fileManager,
        S3Uploader $uploader
    )
    {
        $this->responseHandler = $responseHandler;
        $this->fileManager = $fileManager;
        $this->serializer = $serializer;
        $this->uploader = $uploader;
    }

    /**
     * @Route("/file", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\File\FileConstraintList")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Create entity",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="file", type="string")
     *    )
     * )
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\File::class, groups={"default"})),
     *     description="Return the entity created."
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Tag(name="File")
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface|\LogicException
     * @throws \Doctrine\ORM\ORMException
     * @throws \App\Exceptions\UploadFailedException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $files = $request->files->all();
        $uploadedFile = $this->uploader->uploadFile($files['file'], $connectedUser->getClient());
        $uploadedFile->setUser($connectedUser);

        $this->fileManager->insert($uploadedFile);

        return $this->responseHandler->handle($uploadedFile);
    }
}
