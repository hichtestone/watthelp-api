<?php

declare(strict_types=1);

namespace App\Controller\Pricing;

use App\Entity\Permission;
use App\Entity\Pricing;
use App\Manager\PricingManager;
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
    private PricingManager $pricingManager;
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        PricingManager $pricingManager
    ) {
        $this->pricingManager = $pricingManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/pricing/{id}", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("pricing", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="pricing")
     * @IsGranted(Permission::PRICING_DELETE)
     *
     * @SWG\Response(response=204, description="No Content")
     *
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
        $this->pricingManager->delete($pricing);

        return new JsonResponse(null, 204);
    }
}