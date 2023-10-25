<?php

declare(strict_types=1);

namespace App\Controller\Tax;

use App\Entity\Permission;
use App\Entity\Tax;
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
     * @Route("/tax/{id}", methods={"GET"})
     * @Entity("tax", expr="repository.find(id)")
     * @IsGranted("BELONG_CLIENT", subject="tax")
     * @IsGranted(Permission::TAX_VIEW)
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Tax::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Tax")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Tax $tax): JsonResponse
    {
        return $this->responseHandler->handle($tax);
    }
}