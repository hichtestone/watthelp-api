<?php

declare(strict_types=1);

namespace App\Controller\Pricing;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\Pricing;
use App\Manager\PricingManager;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PutController
{
    private ResponseHandler $responseHandler;
    private PricingManager $pricingManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        PricingManager $pricingManager
    ) {
        $this->responseHandler = $responseHandler;
        $this->pricingManager = $pricingManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/pricing/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @Entity("pricing", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\Pricing\PricingConstraintList", options={"type"="App\Entity\Pricing"})
     * @IsGranted("BELONG_CLIENT", subject="pricing")
     * @IsGranted(Permission::PRICING_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Updates a pricing",
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
     *     description="Returns the entity updated."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Pricing")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, Pricing $pricing): JsonResponse
    {
        $put = $request->request->all();

        $pricing = $this->serializer->denormalize($put, Pricing::class, null, ['object_to_populate' => $pricing]);

        $violations = $this->validator->validate($pricing);
        if (count($violations)) {
            return $this->responseHandler->handleViolations($violations);
        }

        $this->pricingManager->update($pricing);

        return $this->responseHandler->handle($pricing);
    }
}