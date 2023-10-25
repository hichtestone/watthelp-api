<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Analysis;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\Invoice\AnalysisManager;
use App\Request\Validator\Invoice\Analysis\DeleteConstraintList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DeleteController
{
    private AnalysisManager $analysisManager;

    public function __construct(
        AnalysisManager $analysisManager
    ) {
        $this->analysisManager = $analysisManager;
    }

    /**
     * @Route("/invoice/analysis/delete", methods={"POST"})
     * @ConstraintValidator(class=DeleteConstraintList::class)
     * @IsGranted(Permission::ANALYSIS_DELETE)
     *
     * @SWG\Response(response=204, description="No Content")
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Invoice Analysis")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->request->get('filters') ?? [];

        $this->analysisManager->deleteByFilters($connectedUser->getClient(), $filters);

        return new JsonResponse(null, 204);
    }
}