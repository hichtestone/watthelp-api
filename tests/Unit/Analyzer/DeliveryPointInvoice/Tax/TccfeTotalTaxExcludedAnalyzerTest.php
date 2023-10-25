<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Tax;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Tax\TccfeTotalTaxExcludedAnalyzer;
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
use App\Tests\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-tax
 * @group analyzer-tax-tccfe
 */
class TccfeTotalTaxExcludedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = new TccfeTotalTaxExcludedAnalyzer($translationManager, $logger, $taxService, $consumptionService, $conversionService);

        $this->assertEquals('delivery_point_invoice.tax.tccfe.total_tax_excluded', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = new TccfeTotalTaxExcludedAnalyzer($translationManager, $logger, $taxService, $consumptionService, $conversionService);

        $this->assertEquals(AnalyzerInterface::GROUP_TAX, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeHaveTaxInDeliveryPointInvoiceButWithoutAmount()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_TCCFE);
    
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(TccfeTotalTaxExcludedAnalyzer::class, [$taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('ht_amount_missing', ['type' => 'tax', 'tax' => strtoupper(InvoiceTax::TYPE_TAX_TCCFE)]), 'tax.tccfe.total');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfConsumptionServiceThrowAnException()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $tax->setTotal(100_000);
    
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $conversionService = new AmountConversionService();
        $taxService = $this->createMock(TaxService::class);
        $consumptionService = $this->createMock(ConsumptionService::class);
        $consumptionService->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $analyzer = $this->getAnalyzerMock(TccfeTotalTaxExcludedAnalyzer::class, [$taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfTaxServiceThrowAnException()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $tax->setTotal(100_000);
    
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $conversionService = new AmountConversionService();
        $consumptionService = $this->createMock(ConsumptionService::class);
        $consumptionService->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(10);

        $analyzer = $this->getAnalyzerMock(TccfeTotalTaxExcludedAnalyzer::class, [$taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyIfDataDoesNotMatch()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $tax->setTotal(1_000_000);
    
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willReturn([
                'cspe' => [60_000, 80_000],
                'tdcfe' => [60_000, 80_000],
                'tccfe' => [60_000, 80_000],
                'tcfe' => [60_000, 80_000],
                'cta' => [60_000, 80_000]
            ]);

        $conversionService = new AmountConversionService();
        $consumptionService = $this->createMock(ConsumptionService::class);
        $consumptionService->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(10);

        $analyzer = $this->getAnalyzerMock(TccfeTotalTaxExcludedAnalyzer::class, [$taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_AMOUNT,
            transInfo('amount_incorrect', [
                'amount_type' => 'HT',
                'type' => 'tax',
                'tax' => strtoupper(InvoiceTax::TYPE_TAX_TCCFE)
            ]),
            transInfo('tax_applied_rules', [
                'tax' => strtoupper(InvoiceTax::TYPE_TAX_TCCFE),
                'from' => null,
                'to' => null,
                'consumption' => 10,
                'margin' => 0.01,
                'total_min' => 0.05,
                'total_max' => 0.09,
                'min_tax_unit_price' => 0.6,
                'max_tax_unit_price' => 0.8
            ]), '0,10€', null,
            transInfo('expected_value_between_x_y', ['x' => '0,05€', 'y' => '0,09€']),
            'tax.tccfe.total', new AmountDiff(10**5, 11.11, Anomaly::PROFIT_PROVIDER)
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $client = new Client();

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);

        $consumption = new InvoiceConsumption();

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $tax->setTotal(1_000_000);
    
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));

        $taxService = $this->createMock(TaxService::class);
        $taxService->expects($this->once())->method('getTaxAmounts')
            ->with($consumption, $client)
            ->willReturn([
                'cspe' => [90_000, 110_000],
                'tdcfe' => [90_000, 110_000],
                'tccfe' => [90_000, 110_000],
                'tcfe' => [90_000, 110_000],
                'cta' => [90_000, 110_000]
            ]);

        $conversionService = new AmountConversionService();
        $consumptionService = $this->createMock(ConsumptionService::class);
        $consumptionService->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(10);

        $analyzer = $this->getAnalyzerMock(TccfeTotalTaxExcludedAnalyzer::class, [$taxService, $consumptionService, $conversionService]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}