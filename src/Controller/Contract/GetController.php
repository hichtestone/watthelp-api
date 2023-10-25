<?php

declare(strict_types=1);

namespace App\Controller\Contract;

use App\Entity\Contract;
use App\Entity\Permission;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private ResponseHandler $responseHandler;

    public function __construct(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/contract/{id}", methods={"GET"})
     * @Entity("contract", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="contract")
     * @IsGranted(Permission::CONTRACT_VIEW)
     *
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"contract_pricings"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Contract::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Contract")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Contract $contract): JsonResponse
    {
        return $this->responseHandler->handle($contract);
    }
}