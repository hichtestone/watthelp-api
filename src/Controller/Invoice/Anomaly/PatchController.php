<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Anomaly;

use App\Annotation\ConstraintValidator;
use App\Controller\AbstractPatchController;
use App\Entity\Invoice\Anomaly;
use App\Entity\Permission;
use App\Manager\Invoice\AnomalyManager;
use App\Query\Criteria;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PatchController extends AbstractPatchController
{
    private AnomalyManager $anomalyManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        AnomalyManager $anomalyManager,
        ResponseHandler $responseHandler
    ) {
        $this->anomalyManager = $anomalyManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/invoice/anomaly/{id}", methods={"PATCH"})
     * @Entity("anomaly", expr="repository.find(id)")
     * @ConstraintValidator(class="App\Request\Validator\Invoice\Anomaly\OperationConstraintList")
     * @IsGranted(Permission::ANOMALY_EDIT)
     *
     * @SWG\Parameter(
     *     name="operations",
     *     in="body",
     *     description="Updates a property of Anomaly",
     *     required=true,
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(
     *          type="object",
     *          @SWG\Property(property="op", type="string", example={"replace"}),
     *          @SWG\Property(property="path", type="string", example={"/status"}),
     *          @SWG\Property(property="value", type="string")
     *      )
     *    )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Invoice\Anomaly::class, groups={"default"})),
     *     description="Returns the entity updated."
     * ) 
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Invoice Anomaly")
     *
     * 
     * @throws \Exception
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, Anomaly $anomaly): JsonResponse
    {
        $this->handlePatchRequest($request->request->get('operations'), $anomaly, ['status']);

        $this->anomalyManager->update($anomaly);

        return $this->responseHandler->handle($anomaly);
    }
}
