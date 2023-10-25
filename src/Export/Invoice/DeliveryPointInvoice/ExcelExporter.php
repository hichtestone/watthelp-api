<?php

declare(strict_types=1);

namespace App\Export\Invoice\DeliveryPointInvoice;

use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceTax;
use App\Entity\Pricing;
use App\Entity\User;
use App\Export\AbstractExcelExporter;
use App\Export\ExporterInterface;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Message\ExportMessage;
use App\Service\AmountConversionService;
use App\Service\DateFormatService;
use App\Service\S3Uploader;

class ExcelExporter extends AbstractExcelExporter implements ExporterInterface
{
    private const HEADERS = [
        'Référence facture',
        'Référence PDL',
        'Montant HT (€)',
        'Montant TVA (€)',
        'Montant TTC (€)',
        'Type de relevé',
        'Puissance souscrite',
        'Début de consommation',
        'Fin de consommation',
        'Consommation (kWh)',
        'Consommation (€)',
        'Abonnement (€)',
        'TURPE (€)',
        'CSPE (€)',
        'CTA (€)',
        'TCFE (€)',
        'TDCFE (€)',
        'TCCFE (€)'
    ];

    private const TAX_INDICES = [
        InvoiceTax::TYPE_TAX_CSPE  => 14,
        InvoiceTax::TYPE_TAX_CTA   => 15,
        InvoiceTax::TYPE_TAX_TCFE  => 16,
        InvoiceTax::TYPE_TAX_TDCFE => 17,
        InvoiceTax::TYPE_TAX_TCCFE => 18
    ];

    private DeliveryPointInvoiceManager $dpiManager;
    private AmountConversionService $conversionService;

    public function __construct(
        DeliveryPointInvoiceManager $dpiManager,
        S3Uploader $uploader,
        AmountConversionService $conversionService
    ) {
        $this->dpiManager = $dpiManager;
        $this->uploader = $uploader;
        $this->conversionService = $conversionService;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Exception
     */
    public function export(array $filters, User $user): string
    {
        $dpis = $this->dpiManager->findByFilters($user->getClient(), $filters);

        $spreadsheet = $this->initSpreadsheet('Avoirs', self::HEADERS);
        $sheet = $spreadsheet->getActiveSheet();

        $row = 2;
        foreach ($dpis as $dpi) {
            $sheet->setCellValueByColumnAndRow(1, $row, $dpi->getInvoice()->getReference());
            $sheet->setCellValueByColumnAndRow(2, $row, $dpi->getDeliveryPoint()->getReference());
            $sheet->setCellValueByColumnAndRow(3, $row, $this->conversionService->intToHumanReadable($dpi->getAmountHT(), 2, 7, ''));
            $sheet->setCellValueByColumnAndRow(4, $row, $this->conversionService->intToHumanReadable($dpi->getAmountTVA(), 2, 7, ''));
            $sheet->setCellValueByColumnAndRow(5, $row, $this->conversionService->intToHumanReadable($dpi->getAmountTTC(), 2, 7, ''));
            $sheet->setCellValueByColumnAndRow(6, $row, $dpi->getType() === DeliveryPointInvoice::TYPE_REAL ? 'Réel' : 'Estimé');
            $sheet->setCellValueByColumnAndRow(7, $row, $dpi->getPowerSubscribed());

            $consumption = $dpi->getConsumption();
            $sheet->setCellValueByColumnAndRow(8, $row, $consumption->getIndexStartedAt()->format(DateFormatService::EXPORT));
            $sheet->setCellValueByColumnAndRow(9, $row, $consumption->getIndexFinishedAt()->format(DateFormatService::EXPORT));
            $sheet->setCellValueByColumnAndRow(10, $row, $consumption->getQuantity());
            $sheet->setCellValueByColumnAndRow(11, $row, $this->conversionService->intToHumanReadable($consumption->getTotal(), 2, 7, ''));

            $contract = $dpi->getDeliveryPoint()->getContract();
            $subscriptionTotal = $dpi->getSubscription() ? $dpi->getSubscription()->getTotal() : null;
            $subscriptionTotal = $this->conversionService->intToHumanReadable($subscriptionTotal, 2, 7, '');
            if (!$contract || $contract->getType() === Pricing::TYPE_REGULATED) {
                $sheet->setCellValueByColumnAndRow(12, $row, $subscriptionTotal);
            } else {
                $sheet->setCellValueByColumnAndRow(13, $row, $subscriptionTotal);
            }

            foreach ($dpi->getTaxes() as $tax) {
                $sheet->setCellValueByColumnAndRow(self::TAX_INDICES[$tax->getType()], $row, $this->conversionService->intToHumanReadable($tax->getTotal(), 2, 7, ''));
            }

            ++$row;
        }

        $url = $this->upload($spreadsheet, $user->getClient(), 'delivery_point_invoices', 'avoirs');

        return $url;
    }

    public function supports(string $type, string $format): bool
    {
        return $type === ExportMessage::TYPE_DELIVERY_POINT_INVOICE && $format === ExportMessage::FORMAT_EXCEL;
    }
}