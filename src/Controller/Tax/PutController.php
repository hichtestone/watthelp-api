<?php

declare(strict_types=1);

namespace App\Controller\Tax;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Entity\Tax;
use App\Manager\TaxManager;
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
    private TaxManager $taxManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TaxManager $taxManager
    ) {
        $this->responseHandler = $responseHandler;
        $this->taxManager = $taxManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/tax/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @Entity("tax", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\Tax\TaxConstraintList", options={"type"="App\Entity\Tax"})
     * @IsGranted("BELONG_CLIENT", subject="tax")
     * @IsGranted(Permission::TAX_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Updates a tax",
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
     *     description="Returns the entity updated."
     * )
     * 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Tax")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, Tax $tax): JsonResponse
    {
        $put = $request->request->all();

        $tax = $this->serializer->denormalize($put, Tax::class, null, ['object_to_populate' => $tax]);

        $violations = $this->validator->validate($tax);
        if (count($violations)) {
            return $this->responseHandler->handleViolations($violations);
        }

        $this->taxManager->update($tax);

        return $this->responseHandler->handle($tax);
    }
}