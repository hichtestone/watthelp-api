<?php

declare(strict_types=1);

namespace App\Export\Anomaly;

use App\Entity\Invoice\Anomaly;
use App\Entity\User;
use App\Export\ExporterInterface;
use App\Manager\Invoice\AnomalyManager;
use App\Message\ExportMessage;
use App\Service\AmountConversionService;
use App\Service\S3Uploader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter implements ExporterInterface
{
    private array $anomalyHeaders = [
        'Statut',
        'Référence facture',
        'Date facture',
        'Ref point de livraison',
        'Nom point de livraison',
        'Adresse point de livraison',
        'Puissance point de livraison',
        'Montant facture HT',
        'Montant facture TVA',
        'Montant facture TTC',
        'Type anomalie',
        'Date détection anomalie',
        'Valeur actuelle',
        'Valeur attendue',
        'Valeur précédente',
        'Ecart (€)',
        'Ecart (%)',
        'Ref facture précédente',
        'Date facture précédente',
        'Règle',
        'Détail',
        'Profit'
    ];

    private AnomalyManager $anomalyManager;
    private S3Uploader $uploader;
    private AmountConversionService $converter;

    public function __construct(
        AnomalyManager $anomalyManager,
        S3Uploader $uploader,
        AmountConversionService $converter
    ) {
        $this->anomalyManager = $anomalyManager;
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
        $anomalies = $this->anomalyManager->findByFilters($user->getClient(), $filters);

        if (empty($anomalies)) {
            throw new \LogicException('Could not find any anomaly matching the filters: ' . json_encode($filters));
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Alertes');

        foreach ($this->anomalyHeaders as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key+1, 1, $header);
        }

        $row = 2;
        foreach ($anomalies as $anomaly) {
            $itemAnalysis = $anomaly->getItemAnalysis();
            $invoice = $itemAnalysis->getAnalysis() ? $itemAnalysis->getAnalysis()->getInvoice() :  $itemAnalysis->getDeliveryPointInvoiceAnalysis()->getAnalysis() ;

            $sheet->setCellValueByColumnAndRow(1, $row, $anomaly->getStatus());
            $sheet->setCellValueByColumnAndRow(2, $row, $invoice->getReference());
            $sheet->setCellValueByColumnAndRow(3, $row, $invoice->getEmittedAt()->format('d/m/Y'));
            $itemAnalysis = $anomaly->getItemAnalysis();
            $dpia = $itemAnalysis ? $itemAnalysis->getDeliveryPointInvoiceAnalysis() : null;
            $deliveryPointInvoice = $dpia ? $dpia->getDeliveryPointInvoice() : null;

            if (!$deliveryPointInvoice) {
                $sheet->setCellValueByColumnAndRow(8, $row, $this->converter->intToHumanReadable($invoice->getAmountHT()));
                $sheet->setCellValueByColumnAndRow(9, $row, $this->converter->intToHumanReadable($invoice->getAmountTVA()));
                $sheet->setCellValueByColumnAndRow(10, $row, $this->converter->intToHumanReadable($invoice->getAmountTTC()));
            } else {
                $deliveryPoint = $deliveryPointInvoice->getDeliveryPoint();
                if ($deliveryPoint) {
                    $sheet->setCellValueByColumnAndRow(4, $row, $deliveryPoint->getReference());
                    $sheet->setCellValueByColumnAndRow(5, $row, $deliveryPoint->getName());
                    $sheet->setCellValueByColumnAndRow(6, $row, $deliveryPoint->getAddress());
                    $sheet->setCellValueByColumnAndRow(7, $row, $deliveryPoint->getPower());
                    $sheet->setCellValueByColumnAndRow(8, $row, $this->converter->intToHumanReadable($deliveryPointInvoice->getAmountHT()));
                    $sheet->setCellValueByColumnAndRow(9, $row, $this->converter->intToHumanReadable($deliveryPointInvoice->getAmountTVA()));
                    $sheet->setCellValueByColumnAndRow(10, $row, $this->converter->intToHumanReadable($deliveryPointInvoice->getAmountTTC()));
                }
            }

            $sheet->setCellValueByColumnAndRow(11, $row, $anomaly->getType());
            $sheet->setCellValueByColumnAndRow(12, $row, $anomaly->getCreatedAt()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(13, $row, $anomaly->getCurrentValue());
            $sheet->setCellValueByColumnAndRow(14, $row, $anomaly->getExpectedValue());

            if ($anomaly->getOldValue()) {
                $sheet->setCellValueByColumnAndRow(15, $row, $anomaly->getOldValue());
            }

            $sheet->setCellValueByColumnAndRow(16, $row, $this->converter->intToHumanReadable($anomaly->getTotal()));
            $sheet->setCellValueByColumnAndRow(17, $row, $anomaly->getTotalPercentage() ? number_format($anomaly->getTotalPercentage(), 2, ',', ' ') . '%' : null);

            if ($itemAnalysis && $dpia && $dpia->getPreviousDeliveryPointInvoice()) {
                $previousInvoice = $dpia->getPreviousDeliveryPointInvoice()->getInvoice();
                $sheet->setCellValueByColumnAndRow(18, $row, $previousInvoice->getReference());
                $sheet->setCellValueByColumnAndRow(19, $row, $previousInvoice->getEmittedAt()->format('d/m/Y'));
            }

            $sheet->setCellValueByColumnAndRow(20, $row, $anomaly->getAppliedRules());
            $sheet->setCellValueByColumnAndRow(21, $row, $anomaly->getContent());

            $profit = $anomaly->getProfit();
            switch ($profit) {
                case Anomaly::PROFIT_CLIENT:
                    $profit = 'oui';
                    break;
                case Anomaly::PROFIT_PROVIDER:
                    $profit = 'non';
                    break;
                case Anomaly::PROFIT_NONE:
                    $profit = 'aucun';
                    break;
                default:
                    throw new \LogicException('Unhandled profit state: ' . $profit);
            }

            $sheet->setCellValueByColumnAndRow(22, $row, $profit);

            ++$row;
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_end_clean();

        $filepath = $user->getClient()->getId() . '/anomaly';
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('Y_m_d_H:i:s');

        $objectUrl = $this->uploader->uploadContent(
            $filepath,
            $data,
            "anomalies_$now.xlsx",
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        return $objectUrl;
    }

    public function supports(string $type, string $format): bool
    {
        return $type === ExportMessage::TYPE_ANOMALY && $format === ExportMessage::FORMAT_EXCEL;
    }
}