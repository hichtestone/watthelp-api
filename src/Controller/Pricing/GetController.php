<?php

declare(strict_types=1);

namespace App\Controller\Pricing;

use App\Entity\Permission;
use App\Entity\Pricing;
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
     * @Route("/pricing/{id}", methods={"GET"})
     * @Entity("pricing", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="pricing")
     * @IsGranted(Permission::PRICING_VIEW)
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Pricing::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Pricing")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Pricing $pricing): JsonResponse
    {
        return $this->responseHandler->handle($pricing);
    }
}
