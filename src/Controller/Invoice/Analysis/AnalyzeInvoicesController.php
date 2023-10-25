<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Analysis;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Message\AnalyzeInvoiceMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class AnalyzeInvoicesController
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/invoice/analysis", methods={"POST"})
     * @ConstraintValidator(class="App\Request\Validator\Invoice\Analysis\AnalyzeInvoicesConstraintList")
     * @IsGranted(Permission::INVOICE_ANALYZE)
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="Invoice Analysis")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $this->messageBus->dispatch(new AnalyzeInvoiceMessage($data['filters'] ?? [], $connectedUser->getId()));

        return new JsonResponse(null, 201);
    }
}