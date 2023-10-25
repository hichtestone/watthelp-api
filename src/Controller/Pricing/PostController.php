<?php

declare(strict_types=1);

namespace App\Controller\Pricing;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\Pricing;
use App\Manager\PricingManager;
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
    private PricingManager $pricingManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        PricingManager $pricingManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->pricingManager = $pricingManager;
    }

    /**
     * @Route("/pricing", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Pricing\PricingConstraintList", options={"type"="App\Entity\Pricing"})
     * @IsGranted(Permission::PRICING_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a pricing",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="name", type="string", description="required"),
     *         @SWG\Property(property="type", type="string", description="required"),
     *         @SWG\Property(property="subscription_price", type="integer", description=""),
     *         @SWG\Property(property="consumption_base_price", type="integer", description="required"),
     *         @SWG\Property(property="started_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00"),
     *         @SWG\Property(property="finished_at", type="string", description="ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Pricing::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Pricing")
     * 
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $pricing = $this->serializer->denormalize($data, Pricing::class);
        $pricing->setClient($connectedUser->getClient());

        $violations = $this->validator->validate($pricing);
        if (count($violations)) {
            return $this->responseHandler->handleViolations($violations);
        }

        $this->pricingManager->insert($pricing);

        return $this->responseHandler->handle($pricing, [], 201);
    }
}