<?php

declare(strict_types=1);

namespace App\Controller\Tax;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\Tax;
use App\Manager\TaxManager;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private TaxManager $taxManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TaxManager $taxManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->taxManager = $taxManager;
    }

    /**
     * @Route("/tax", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Tax\TaxConstraintList", options={"type"="App\Entity\Tax"})
     * @IsGranted(Permission::TAX_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a tax",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="cspe", type="integer", description="required"),
     *         @SWG\Property(property="tdcfe", type="integer", description="required"),
     *         @SWG\Property(property="tccfe", type="integer", description="required"),
     *         @SWG\Property(property="cta", type="integer", description="required"),
     *         @SWG\Property(property="started_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00"),
     *         @SWG\Property(property="finished_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Tax::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Tax")
     * 
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $tax = $this->serializer->denormalize($data, Tax::class);
        $tax->setClient($connectedUser->getClient());

        $violations = $this->validator->validate($tax);
        if (count($violations)) {
            return $this->responseHandler->handleViolations($violations);
        }

        $this->taxManager->insert($tax);

        return $this->responseHandler->handle($tax, [], 201);
    }
}