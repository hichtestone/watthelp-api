<?php

declare(strict_types=1);

namespace App\Controller\Contract\Refer;

use App\Entity\Contract;
use App\Entity\Permission;
use App\Manager\ContractManager;
use App\Response\ResponseHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private ContractManager $contractManager;

    public function __construct(
        ResponseHandler $responseHandler,
        ContractManager $contractManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->contractManager = $contractManager;
    }

    /**
     * @Route("/contract/{id}/refer", methods={"POST"})
     * @Entity("contract", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="contract")
     * @IsGranted(Permission::CONTRACT_EDIT)
     *
     * @SWG\Response(response=204, description="no content")
     * 
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * 
     * @SWG\Tag(name="Contract")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Contract $contract): JsonResponse
    {
        $this->contractManager->updateContractsFromContract($contract);

        return $this->responseHandler->handle(null, [], 204);
    }
}