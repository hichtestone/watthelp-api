<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Analysis;

use App\Entity\Invoice\Analysis;
use App\Entity\Permission;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private ResponseHandler $responseHandler;

    public function __construct(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/invoice/analysis/{id}", methods={"GET"})
     * @Entity("analysis", expr="repository.find(id)")
     * @IsGranted(Permission::ANALYSIS_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"analysis_invoice","analysis_delivery_point_invoice_analyses","analysis_item_analyses"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Invoice\Analysis::class, groups={"default"})),
     *     description=""
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Invoice Analysis")
     * 
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Analysis $analysis, UserInterface $connectedUser): JsonResponse
    {
        if ($analysis->getInvoice()->getClient()->getId() !== $connectedUser->getClient()->getId()) {
            throw new AccessDeniedHttpException('Analysis belongs to a different client');
        }
        return $this->responseHandler->handle($analysis);
    }
}