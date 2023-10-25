<?php

declare(strict_types=1);

namespace App\Controller\ImportReport;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\ImportReportManager;
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
    private ImportReportManager $importReportManager;
    private ResponseHandler $responseHandler;

    public function __construct(
        ImportReportManager $importReportManager,
        ResponseHandler $responseHandler
    ) {
        $this->importReportManager = $importReportManager;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @Route("/import-report", methods={"GET"})
     * @ConstraintValidator(class="App\Request\Validator\ImportReport\FilterConstraintList", options={"type"="App\Entity\ImportReport"})
     * @IsGranted(Permission::IMPORT_REPORT_VIEW)
     * 
     * @SWG\Parameter(name="X-Expand-Data", in="header", required=false, type="string", description="Expand data.", enum={"import_report_invoices"})
     * 
     * @SWG\Parameter(name="page", in="query", required=false, type="integer", minimum="1", description="Page.")
     * @SWG\Parameter(name="per_page", in="query", required=false, type="integer", minimum="1", maximum="100", description="Number of element per page.")
     * @SWG\Parameter(name="sort_order", in="query", required=false, type="string", enum={"asc","desc"}, description="Sort order.")
     * @SWG\Parameter(name="sort", in="query", required=false, type="string", enum={"id","created_at"})
     *
     * @SWG\Parameter(name="filters[id]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[exclude_ids]", in="query", type="array", @SWG\Items(type="integer"))
     * @SWG\Parameter(name="filters[status]", in="query", type="string", enum={"ok", "error"})
     * 
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(
     *      type="object",
     *      @SWG\Property(property="count", type="integer"),
     *      @SWG\Property(property="page", type="integer"),
     *      @SWG\Property(property="per_page", type="integer"),
     *      @SWG\Property(property="data", type="array", @SWG\Items(type="object", ref=@Model(type=App\Entity\ImportReport::class, groups={"default"}))),
     *     ),
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="ImportReport")
     * 
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Request $request, Pagination $pagination, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->query->has('filters') ? $request->query->all('filters') : [];
        $filters['user'] = $connectedUser;

        $data = $this->importReportManager->findByFilters($filters, $pagination);

        return $this->responseHandler->handle($data);
    }
}