<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Tax;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Tax\CtaTotalTaxExcludedAnalyzer;
use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Invoice\InvoiceTax;
use App\Exceptions\IgnoreException;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\ConsumptionService;
use App\Service\LogService;
use App\Service\TaxService;
use App\Service\TurpeService;
use App\Tests\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-tax
 * @group analyzer-tax-cta-total-tax-excluded
 */
class CtaTotalTaxExcludedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = $this->createMock(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $turpeService = $this->createMock(TurpeService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = new CtaTotalTaxExcludedAnalyzer($translationManager, $logger, $turpeService, $taxService, $consumptionService, $conversionService);

        $this->assertEquals('delivery_point_invoice.tax.cta.total_tax_excluded', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = $this->createMock(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $turpeService = $this->createMock(TurpeService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = new CtaTotalTaxExcludedAnalyzer($translationManager, $logger, $turpeService, $taxService, $consumptionService, $conversionService);

        $this->assertEquals(AnalyzerInterface::GROUP_TAX, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveCTATaxInDeliveryPointInvoice()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $turpeService = $this->createMock(TurpeService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(CtaTotalTaxExcludedAnalyzer::class, [$turpeService, $taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('cta_missing'), 'tax.cta');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeHaveCTATaxInDeliveryPointInvoiceButWithoutAmount()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $turpeService = $this->createMock(TurpeService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(CtaTotalTaxExcludedAnalyzer::class, [$turpeService, $taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('ht_amount_missing', ['type' => 'tax', 'tax' => strtoupper(InvoiceTax::TYPE_TAX_CTA)]), 'tax.cta.total');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfTaxServiceThrowAnException()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);
        $tax->setTotal(100000);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $turpeService = $this->createMock(TurpeService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(CtaTotalTaxExcludedAnalyzer::class, [$turpeService, $taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfTurpeServiceThrowAnException()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2016-08-01'));
        $consumption->setIndexFinishedAt(new \DateTime('2017-07-31'));
        $consumption->setQuantity(100);

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);
        $tax->setTotal(100000);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('2');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willReturn(['cta' => [2704, 2704]]);

        $turpeService = $this->createMock(TurpeService::class);
        $turpeService->expects($this->once())->method('getTurpeInterval')
            ->with('2', new \DateTime('2016-08-01'), new \DateTime('2017-07-31'), 100)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(CtaTotalTaxExcludedAnalyzer::class, [$turpeService, $taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyIfDataDoesNotMatch()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt($indexStartedAt = new \DateTime('2020-03-14'));
        $consumption->setIndexFinishedAt($indexFinishedAt = new \DateTime('2020-05-14'));
        $consumption->setQuantity(100);

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);
        $tax->setTotal(1342*10**5); // 13,42€

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willReturn([
                'cspe' => [70_000, 90_000],
                'tdcfe' => [70_000, 90_000],
                'tccfe' => [70_000, 90_000],
                'tcfe' => [70_000, 90_000],
                'cta' => [2704, 2704]
            ]);

        $turpeService = new TurpeService();
        $consumptionService = $this->createMock(ConsumptionService::class);

        $appliedRules = transInfo('cta_applied_rules', [
            'from' => $indexStartedAt,
            'to' => $indexFinishedAt,
            'min_cta' => 27.04,
            'max_cta' => 27.04,
            'consumption' => 100,
            'number_of_days' => $indexStartedAt->diff($indexFinishedAt)->days,
            'min_cg' => 2.13,
            'max_cg' => 2.13,
            'min_cc' => 3.41,
            'max_cc' => 3.41,
            'min_fixed_cs' => 40.51,
            'max_fixed_cs' => 40.51,
            'min_turpe' => 46.05,
            'max_turpe' => 46.05,
            'margin' => 0.01,
            'total_min' => 12.44,
            'total_max' => 12.46
        ]);

        $analyzer = $this->getAnalyzerMock(CtaTotalTaxExcludedAnalyzer::class, [$turpeService, $taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_AMOUNT,
            transInfo('amount_incorrect', [
                'amount_type' => 'HT',
                'type' => 'tax',
                'tax' => strtoupper(InvoiceTax::TYPE_TAX_CTA)
            ]),
            $appliedRules, '13,42€', null, transInfo('expected_value_between_x_y', ['x' => '12,44€', 'y' => '12,46€']),
            'tax.cta.total', new AmountDiff(9592137, 7.7, Anomaly::PROFIT_PROVIDER)
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2020-03-14'));
        $consumption->setIndexFinishedAt(new \DateTime('2020-05-14'));
        $consumption->setQuantity(100);

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);
        $tax->setTotal(1245*10**5); // 12,45€

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willReturn([
                'cspe' => [70_000, 90_000],
                'tdcfe' => [70_000, 90_000],
                'tccfe' => [70_000, 90_000],
                'tcfe' => [70_000, 90_000],
                'cta' => [2704, 2704]
            ]);

        $turpeService = new TurpeService();
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(CtaTotalTaxExcludedAnalyzer::class, [$turpeService, $taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}