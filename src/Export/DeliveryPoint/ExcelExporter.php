<?php

declare(strict_types=1);

namespace App\Export\DeliveryPoint;

use App\Entity\User;
use App\Export\ExporterInterface;
use App\Manager\DeliveryPointManager;
use App\Message\ExportMessage;
use App\Service\DateFormatService;
use App\Service\S3Uploader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter implements ExporterInterface
{
    private array $headers = [
        'Nom',
        'Référence',
        'Code',
        'Adresse',
        'Latitude',
        'Longitude',
        'Référence compteur',
        'Puissance',
        'Description',
        'Dans périmètre',
        'Date périmètre'
    ];

    private DeliveryPointManager $deliveryPointManager;
    private S3Uploader $uploader;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        S3Uploader $uploader
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->uploader = $uploader;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function export(array $filters, User $user): string
    {
        $deliveryPoints = $this->deliveryPointManager->findByFilters($user->getClient(), $filters);

        if (empty($deliveryPoints)) {
            throw new \LogicException('Could not find any delivery point matching the filters: ' . json_encode($filters));
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Points de livraison');

        foreach ($this->headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key+1, 1, $header);
        }

        $row = 2;
        foreach ($deliveryPoints as $deliveryPoint) {

            $sheet->setCellValueByColumnAndRow(1, $row, $deliveryPoint->getName());
            $sheet->setCellValueByColumnAndRow(2, $row, $deliveryPoint->getReference());
            $sheet->setCellValueByColumnAndRow(3, $row, $deliveryPoint->getCode());
            $sheet->setCellValueByColumnAndRow(4, $row, $deliveryPoint->getAddress());
            $sheet->setCellValueByColumnAndRow(5, $row, $deliveryPoint->getLatitude());
            $sheet->setCellValueByColumnAndRow(6, $row, $deliveryPoint->getLongitude());
            $sheet->setCellValueByColumnAndRow(7, $row, $deliveryPoint->getMeterReference());
            $sheet->setCellValueByColumnAndRow(8, $row, $deliveryPoint->getPower());
            $sheet->setCellValueByColumnAndRow(9, $row, $deliveryPoint->getDescription());
            $sheet->setCellValueByColumnAndRow(10, $row, $deliveryPoint->getIsInScope() ? 'oui' : 'non');
            $sheet->setCellValueByColumnAndRow(11, $row, $deliveryPoint->getScopeDate() ? $deliveryPoint->getScopeDate()->format(DateFormatService::IMPORT) : '');

            ++$row;
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_end_clean();

        $filepath = $user->getClient()->getId() . '/delivery_point';
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('Y_m_d_H:i:s');

        $objectUrl = $this->uploader->uploadContent(
            $filepath,
            $data,
            "delivery_points_$now.xlsx",
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        return $objectUrl;
    }

    public function supports(string $type, string $format): bool
    {
        return $type === ExportMessage::TYPE_DELIVERY_POINT && $format === ExportMessage::FORMAT_EXCEL;
    }
}