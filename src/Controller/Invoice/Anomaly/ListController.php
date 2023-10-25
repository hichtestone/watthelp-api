<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Anomaly;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\Invoice\AnomalyManager;
use App\Request\Pagination;
use App\Response\ResponseHandler;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ListController
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
     * @Route("/invoice/anomaly", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\Invoice\Anomaly\FilterConstraintList", options={"type"="App\Entity\Invoice\Anomaly"})
     * @IsGranted(Permission::ANOMALY_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"anomaly_item_analysis","anomaly_notes"})
     * 
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","invoices","status","content","total","created_at"})
     * @SWG\Parameter(name="filters[id]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[invoices]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[invoice_reference]", in="query", type="string")
     * @SWG\Parameter(name="filters[status]", in="query", type="string")
     * @SWG\Parameter(name="filters[created][to]", in="query", type="string")
     * @SWG\Parameter(name="filters[created][from]", in="query", type="string")
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\Invoice\Anomaly::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Invoice Anomaly")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->all('filters') : [];

        $data = $this->anomalyManager->findByFilters($connectedUser->getClient(), $filters, $pagination);

        return $this->responseHandler->handle($data);
    }
}