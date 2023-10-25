<?php

declare(strict_types=1);

namespace App\Controller\Stats\Consumption;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\DeliveryPointManager;
use App\Manager\Invoice\InvoiceConsumptionManager;
use App\Response\ResponseHandler;
use App\Service\ConsumptionService;
use App\Service\ConsumptionService\Context;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
{
    private DeliveryPointManager $deliveryPointManager;
    private InvoiceConsumptionManager $invoiceConsumptionManager;
    private ConsumptionService $consumptionService;
    private ResponseHandler $responseHandler;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        InvoiceConsumptionManager $invoiceConsumptionManager,
        ConsumptionService $consumptionService,
        ResponseHandler $responseHandler
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->invoiceConsumptionManager = $invoiceConsumptionManager;
        $this->consumptionService = $consumptionService;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/stats/consumption", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Stats\Consumption\StatsConstraintList")
     * @IsGranted({Permission::DASHBOARD_VIEW, Permission::INVOICE_VIEW})
     *
     * @SWG\Response(
     *     response=200,
     *     description="consumption statistics"
     * )
     *
     * @SWG\Tag(name="Stats")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $params = $request->query->all();
        $context = new Context($params);
        
        if (!empty($params['delivery_point_filters'] ?? [])) {
            $deliveryPoints = $this->deliveryPointManager->findByFilters($connectedUser->getClient(), $params['delivery_point_filters']);
            $context->setDeliveryPoints(iterator_to_array($deliveryPoints));
        }

        $consumptions = $this->consumptionService->getTotalConsumptionOfYears($connectedUser->getClient(), $context);

        return $this->responseHandler->handle($consumptions);
    }
}