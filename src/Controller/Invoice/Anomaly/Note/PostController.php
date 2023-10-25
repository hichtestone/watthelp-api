<?php

declare(strict_types=1);

namespace App\Controller\Invoice\Anomaly\Note;

use App\Annotation\ConstraintValidator;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\Anomaly\Note;
use App\Entity\Permission;
use App\Manager\Invoice\Anomaly\NoteManager;
use App\Response\ResponseHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController
{
    private ResponseHandler $responseHandler;
    private SerializerInterface $serializer;
    private NoteManager $noteManager;

    public function __construct(
        ResponseHandler $responseHandler,
        SerializerInterface $serializer,
        NoteManager $noteManager
    )
    {
        $this->responseHandler = $responseHandler;
        $this->serializer = $serializer;
        $this->noteManager = $noteManager;
    }

    /**
     * @Route("/invoice/anomaly/{id}/note", methods={"POST"})
     * @Entity("anomaly", expr="repository.find(id)")
     * 
     * @ConstraintValidator(class="App\Request\Validator\Invoice\Anomaly\Note\NoteConstraintList", options={"type"="App\Entity\Invoice\Anomaly\Note"})
     * @IsGranted(Permission::ANOMALY_NOTE_EDIT)
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Creates a note",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="content", type="string", description="required"),
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     @SWG\Schema(type="object", ref=@Model(type=App\Entity\Invoice\Anomaly\Note::class, groups={"default"})),
     *     description="Returns the entity created."
     * )
     *
     * @SWG\Response(response=400, ref="#/definitions/bad_request")
     * @SWG\Response(response=404, ref="#/definitions/not_found")
     * @SWG\Tag(name="Note")
     *
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request, Anomaly $anomaly, UserInterface $connectedUser): JsonResponse
    {
        $data = $request->request->all();

        $note = $this->serializer->denormalize($data, Note::class);
        $note->setAnomaly($anomaly);
        $note->setUser($connectedUser);

        $this->noteManager->insert($note);

        return $this->responseHandler->handle($note, [], 201);
    }
}