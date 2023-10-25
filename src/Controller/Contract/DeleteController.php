<?php

declare(strict_types=1);

namespace App\Controller\Contract;

use App\Entity\Contract;
use App\Entity\Permission;
use App\Manager\ContractManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteController
{
    private ContractManager $contractManager;
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        ContractManager $contractManager
    ) {
        $this->contractManager = $contractManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/contract/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("contract", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="contract")
     * @IsGranted(Permission::CONTRACT_DELETE)
     *
     * @SWG\Response(response=204, description="No Content")
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Contract")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, Contract $contract): JsonResponse
    {
        $this->contractManager->delete($contract);

        return new JsonResponse(null, 204);
    }
}