<?php

declare(strict_types=1);

namespace App\Controller\Contract;

use App\Annotation\ConstraintValidator;
use App\Entity\Contract;
use App\Entity\Permission;
use App\Manager\ContractManager;
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
    private ContractManager $contractManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        ContractManager $contractManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->contractManager = $contractManager;
    }

    /**
     * @Route("/contract", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Contract\ContractConstraintList", options={"type"="App\Entity\Contract"})
     * @IsGranted(Permission::CONTRACT_EDIT)
     *
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"contract_pricings"})
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a contract",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="reference", type="string", description="required"),
     *         @SWG\Property(property="provider", type="string", description="required"),
     *         @SWG\Property(property="type", type="string", description="required"),
     *         @SWG\Property(property="invoice_period", type="string", description="optional"),
     *         @SWG\Property(property="started_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00"),
     *         @SWG\Property(property="finished_at", type="string", description="ISO 8601 - ex: 2020-07-16T19:20:05+01:00"),
     *         @SWG\Property(property="pricing_ids", type="array", @SWG\Items(type="integer"), description="array of pricing ids"),
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Contract::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Contract")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $contract = $this->serializer->denormalize($data, Contract::class);
        $contract->setClient($connectedUser->getClient());

        $this->contractManager->insert($contract);

        return $this->responseHandler->handle($contract, [], 201);
    }
}
