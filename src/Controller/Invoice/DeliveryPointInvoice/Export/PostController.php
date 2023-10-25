<?php

declare(strict_types=1);

namespace App\Controller\Invoice\DeliveryPointInvoice\Export;

use App\Annotation\ConstraintValidator;
use App\Entity\Permission;
use App\Message\ExportMessage;
use App\Request\Validator\Invoice\DeliveryPointInvoice\ExportConstraintList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PostController
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/delivery-point-invoice/export", methods={"POST"})
     * @ConstraintValidator(class=ExportConstraintList::class)
     * @IsGranted({Permission::INVOICE_VIEW,Permission::DELIVERY_POINT_VIEW})
     *
     * @SWG\Response(
     *     response=201,
     *     description=""
     * )
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Tag(name="DeliveryPointInvoice")
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function __invoke(Request $request, UserInterface $connectedUser): JsonResponse
    {
        // for now we only export the credit notes
        $filters = ['is_credit_note' => true];

        $message = new ExportMessage(
            $connectedUser->getId(),
            ExportMessage::TYPE_DELIVERY_POINT_INVOICE,
            $filters,
            ExportMessage::FORMAT_EXCEL
        );

        $this->messageBus->dispatch($message);

        return new JsonResponse(null, 201);
    }
}