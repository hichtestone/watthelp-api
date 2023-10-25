<?php

declare(strict_types=1);

namespace App\Controller\Stats\Amounts;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Request\Validator\Stats\Amounts\StatsConstraintList;
use App\Response\ResponseHandler;
use App\Service\ConsumptionService;
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
    private ConsumptionService $consumptionService;
    private ResponseHandler $responseHandler;

    public function __construct(
        ConsumptionService $consumptionService,
        ResponseHandler $responseHandler
    ) {
        $this->consumptionService = $consumptionService;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/stats/amounts", methods={"GET"})
     * @ConstraintValidator(class=StatsConstraintList::class)
     * @IsGranted({Permission::DASHBOARD_VIEW,Permission::INVOICE_VIEW,Permission::TAX_VIEW})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the total amount of the consumptions, subscriptions and taxes in the given period"
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
        $start = isset($params['start']) ? \DateTime::createFromFormat('Y-m-d', $params['start']) : null;
        $end = isset($params['end']) ? \DateTime::createFromFormat('Y-m-d', $params['end']) : null;

        $result = $this->consumptionService->getTotalAmountsBetweenInterval($connectedUser->getClient(), $start, $end);

        return $this->responseHandler->handle($result);
    }
}