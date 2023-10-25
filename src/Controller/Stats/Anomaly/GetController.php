<?php

declare(strict_types=1);

namespace App\Controller\Stats\Anomaly;

use App\Entity\Permission;
use App\Manager\Invoice\AnomalyManager;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class GetController
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
     * @Route("/stats/anomaly", methods={"GET"})
     * @IsGranted({Permission::DASHBOARD_VIEW,Permission::ANOMALY_VIEW})
     *
     * @SWG\Response(
     *     response=200,
     *     description="anomaly statistics",
     *     examples={
     *         "application/json": {
     *             "num_anomalies": "4",
     *             "stat_ignored": "1",
     *             "stat_unsolved": "0",
     *             "stat_processing": "1",
     *             "stat_solved": "2"
     *         }
     *     }
     * )

     * 
     * @SWG\Tag(name="Stats")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $stats = $this->anomalyManager->getStats($connectedUser->getClient());

        return $this->responseHandler->handle($stats);
    }
}