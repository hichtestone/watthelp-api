<?php

declare(strict_types=1);

namespace App\Export\Pricing;

use App\Entity\Pricing;
use App\Entity\User;
use App\Export\ExporterInterface;
use App\Manager\PricingManager;
use App\Message\ExportMessage;
use App\Service\AmountConversionService;
use App\Service\S3Uploader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter implements ExporterInterface
{
    private array $pricingHeaders = [
        'Nom',
        'Type',
        'Date de début',
        'Date de fin',
        'Consommation (cts €/kWh)',
        'Abonnement (€/kVA/mois)',
    ];

    private PricingManager $pricingManager;
    private S3Uploader $uploader;
    private AmountConversionService $converter;

    public function __construct(
        PricingManager $pricingManager,
        S3Uploader $uploader,
        AmountConversionService $converter
    )
    {
        $this->pricingManager = $pricingManager;
        $this->uploader = $uploader;
        $this->converter = $converter;
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
        $pricings = $this->pricingManager->findByFilters($user->getClient(), $filters);

        if (empty($pricings)) {
            throw new \LogicException('Could not find any pricing matching the filters: ' . json_encode($filters));
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tarifs');

        foreach ($this->pricingHeaders as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
        }

        $row = 2;
        foreach ($pricings as $pricing) {
            $sheet->setCellValueByColumnAndRow(1, $row, $pricing->getName());

            $type = $pricing->getType();
            switch ($type) {
                case Pricing::TYPE_NEGOTIATED:
                    $type = 'Offre de marché';
                    break;
                case Pricing::TYPE_REGULATED:
                    $type = 'TRV';
                    break;
                default:
                    throw new \LogicException('Unhandled type state: ' . $type);
            }

            $sheet->setCellValueByColumnAndRow(2, $row, $type);


            $sheet->setCellValueByColumnAndRow(3, $row, $pricing->getStartedAt()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(4, $row, $pricing->getFinishedAt()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(5, $row, $this->converter->intToHumanReadable($pricing->getConsumptionBasePrice(), 3, 5, '') ?? '');
            $sheet->setCellValueByColumnAndRow(6, $row, $this->converter->intToHumanReadable($pricing->getSubscriptionPrice(), 2, 7, '') ?? '');

            ++$row;
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_end_clean();

        $filepath = $user->getClient()->getId() . '/pricing';
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('Y_m_d_H:i:s');

        $objectUrl = $this->uploader->uploadContent(
            $filepath,
            $data,
            "pricings_$now.xlsx",
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        return $objectUrl;
    }

    public function supports(string $type, string $format): bool
    {
        return $type === ExportMessage::TYPE_PRICING && $format === ExportMessage::FORMAT_EXCEL;
    }
}
