<?php

declare(strict_types=1);

namespace App\Controller\Stats;

use App\Entity\Permission;
use App\Manager\DeliveryPointManager;
use App\Manager\InvoiceManager;
use App\Manager\Invoice\AnomalyManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class CountController
{
    private DeliveryPointManager $deliveryPointManager;
    private InvoiceManager $invoiceManager;
    private AnomalyManager $anomalyManager;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        InvoiceManager $invoiceManager,
        AnomalyManager $anomalyManager
    )
    {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->invoiceManager = $invoiceManager;
        $this->anomalyManager = $anomalyManager;
    }

    /**
     * @Route("/stats/count", methods={"GET"})
     * @IsGranted({Permission::DASHBOARD_VIEW,Permission::DELIVERY_POINT_VIEW,Permission::INVOICE_VIEW,Permission::ANOMALY_VIEW})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Global statistics"
     * )
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Stats")
     *
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $client = $connectedUser->getClient();

        $countInvoice = $this->invoiceManager->getCountInvoice($client);
        $countAnomaly = $this->anomalyManager->getCountAnomalies($client);
        $countDeliveryPoint = $this->deliveryPointManager->getCountDeliveryPoints($client);

        $data = [
            'invoices' => $countInvoice,
            'anomalies' => $countAnomaly,
            'delivery_points' => $countDeliveryPoint ?? []
        ];

        return new JsonResponse($data);
    }
}
