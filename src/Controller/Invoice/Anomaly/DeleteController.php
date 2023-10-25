<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Anomaly;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Manager\Invoice\AnomalyManager;
use App\Request\Validator\Invoice\Anomaly\DeleteConstraintList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DeleteController
{
    private AnomalyManager $anomalyManager;

    public function __construct(
        AnomalyManager $anomalyManager
    ) {
        $this->anomalyManager = $anomalyManager;
    }

    /**
     * @Route("/invoice/anomaly/delete", methods={"POST"})
     * This is a POST because apparently Angular can't send a body in a DELETE call 
     * @ConstraintValidator(class=DeleteConstraintList::class)
     * @IsGranted(Permission::ANOMALY_DELETE)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(type="object"),
     *     description="Deletes anomalies",
     *     required=true
     * )
     * 
     * @SWG\Response(response=204, description="No Content")
     *
     * @SWG\Response(response=403, ref="#/definitions/forbidden")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Invoice Anomaly")
     * 
     * @throws ExceptionInterface
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $filters = $request->request->get('filters') ?? [];

        $this->anomalyManager->deleteByFilters($connectedUser->getClient(), $filters);

        return new JsonResponse(null, 204);
    }
}