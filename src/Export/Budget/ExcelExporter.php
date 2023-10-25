<?php

declare(strict_types=1);

namespace App\Export\Budget;

use App\Entity\User;
use App\Export\ExporterInterface;
use App\Manager\BudgetManager;
use App\Message\ExportMessage;
use App\Service\AmountConversionService;
use App\Service\S3Uploader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter implements ExporterInterface
{
    private array $budgetHeaders = [
        'N° Budget',
        'Client',
        'Année',
        'Nombre d\'heures de fonctionnement',
        'Prix moyen',
        'Point de livraison (Référence)',
        'Puissance installée',
        'Pourcentage appareillage (%)',
        'Gradation (%)',
        'Nombre d\'heures de gradation',
        'Consommation avant travaux',
        'Travaux de rénovation (Oui/Non)',
        'Date de réalisation des travaux',
        'Puissance installée après travaux',
        'Pourcentage appareillage après travaux (%)',
        'Gradation après travaux (%)',
        'Nombre d\'heures de gradation après travaux',
        'Consommation après travaux',
        'Consommation totale PDL',
        'Budget total PDL (€)'
    ];

    private BudgetManager $budgetManager;
    private S3Uploader $uploader;
    private AmountConversionService $converter;

    public function __construct(
        BudgetManager $budgetManager,
        S3Uploader $uploader,
        AmountConversionService $converter
    ) {
        $this->budgetManager = $budgetManager;
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
        $budgets = $this->budgetManager->findByFilters($user->getClient(), $filters);

        if (empty($budgets)) {
            throw new \LogicException('Could not find any budget matching the filters: ' . json_encode($filters));
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Budgets');

        foreach ($this->budgetHeaders as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key+1, 1, $header);
        }

        $row = 2;
        foreach ($budgets as $budget) {
            if (count($budget->getDeliveryPointBudgets()) > 0) {
                foreach ($budget->getDeliveryPointBudgets() as $dpBudget) {
                    $sheet = $spreadsheet->getSheet(0);
                    $sheet->setCellValueByColumnAndRow(1, $row, $budget->getId());
                    $sheet->setCellValueByColumnAndRow(2, $row, $budget->getClient()->getName());
                    $sheet->setCellValueByColumnAndRow(3, $row, $budget->getYear());
                    $sheet->setCellValueByColumnAndRow(4, $row, $budget->getTotalHours());
                    $sheet->setCellValueByColumnAndRow(5, $row, $this->converter->intToHumanReadable($budget->getAveragePrice()));
                    $sheet->setCellValueByColumnAndRow(6, $row, $dpBudget->getDeliveryPoint()->getReference());
                    $sheet->setCellValueByColumnAndRow(7, $row, $dpBudget->getInstalledPower() . 'kWh');
                    $sheet->setCellValueByColumnAndRow(8, $row, $this->converter->percentageToHumanReadable($dpBudget->getEquipmentPowerPercentage()));
                    $sheet->setCellValueByColumnAndRow(9, $row, $this->converter->percentageToHumanReadable($dpBudget->getGradation()));
                    $sheet->setCellValueByColumnAndRow(10, $row, $dpBudget->getGradationHours());
                    $sheet->setCellValueByColumnAndRow(11, $row, $this->converter->intToHumanReadable($dpBudget->getSubTotalConsumption(), 2, 2, 'kWh'));
                    $sheet->setCellValueByColumnAndRow(12, $row, $dpBudget->isRenovation() ? 'Oui' : 'Non');
                    $sheet->setCellValueByColumnAndRow(13, $row, $dpBudget->getRenovatedAt() ? $dpBudget->getRenovatedAt()->format('d/m/Y') : '');
                    $sheet->setCellValueByColumnAndRow(14, $row, $dpBudget->getNewInstalledPower() . 'kWh');
                    $sheet->setCellValueByColumnAndRow(15, $row, $this->converter->percentageToHumanReadable($dpBudget->getNewEquipmentPowerPercentage()));
                    $sheet->setCellValueByColumnAndRow(16, $row, $this->converter->percentageToHumanReadable($dpBudget->getNewGradation()));
                    $sheet->setCellValueByColumnAndRow(17, $row, $dpBudget->getNewGradationHours());
                    $sheet->setCellValueByColumnAndRow(18, $row, $this->converter->intToHumanReadable($dpBudget->getNewSubTotalConsumption(), 2, 2, 'kWh'));
                    $sheet->setCellValueByColumnAndRow(19, $row, $this->converter->intToHumanReadable($dpBudget->getTotalConsumption(), 2, 2, 'kWh'));
                    $sheet->setCellValueByColumnAndRow(20, $row, $this->converter->intToHumanReadable($dpBudget->getTotal()));

                    ++$row;
                }
            }
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_end_clean();

        $filepath = $user->getClient()->getId() . '/budget';
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('Y_m_d_H:i:s');

        $objectUrl = $this->uploader->uploadContent(
            $filepath,
            $data,
            "budgets_$now.xlsx",
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        return $objectUrl;
    }

    public function supports(string $type, string $format): bool
    {
        return $type === ExportMessage::TYPE_BUDGET && $format === ExportMessage::FORMAT_EXCEL;
    }
}