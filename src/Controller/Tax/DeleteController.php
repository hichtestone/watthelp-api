<?php

declare(strict_types=1);

namespace App\Controller\Tax;

use App\Entity\Permission;
use App\Entity\Tax;
use App\Manager\TaxManager;
use Nelmio\ApiDocBundle\Annotation\Model;
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
    private TaxManager $taxManager;
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        TaxManager $taxManager
    ) {
        $this->taxManager = $taxManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/tax/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("tax", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="tax")
     * @IsGranted(Permission::TAX_DELETE)
     *
     * @SWG\Response(response=204, description="No Content")
     *
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
        $this->taxManager->delete($tax);

        return new JsonResponse(null, 204);
    }
}