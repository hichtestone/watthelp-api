<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\TotalTaxAnalyzer;
use App\Entity\Client;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Invoice\InvoiceSubscription;
use App\Entity\Invoice\InvoiceTax;
use App\Entity\Pricing;
use App\Exceptions\IgnoreException;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\LogService;
use App\Service\TurpeService;
use App\Tests\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-total-tax
 */
class TotalTaxAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $turpeService = $this->createMock(TurpeService::class);

        $analyzer = new TotalTaxAnalyzer($translationManager, $logger, $turpeService, new AmountConversionService());

        $this->assertEquals('delivery_point_invoice.total_tax', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $turpeService = $this->createMock(TurpeService::class);

        $analyzer = new TotalTaxAnalyzer($translationManager, $logger, $turpeService, new AmountConversionService());

        $this->assertEquals(AnalyzerInterface::GROUP_DEFAULT, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfConsumptionPeriodInvalid()
    {
        $contract = new Contract();
        $contract->setType(Pricing::TYPE_NEGOTIATED);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setContract($contract);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2020-01-01'));
        $consumption->setIndexFinishedAt(new \DateTime('2020-01-02'));
        $consumption->setQuantity(100);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $turpeService = $this->createMock(TurpeService::class);
        $analyzer = $this->getAnalyzerMock(TotalTaxAnalyzer::class, [$turpeService, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_period_must_be_at_least_one_month_long'), 'consumption');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfTurpeServiceThrowAnException()
    {
        $client = new Client();

        $contract = new Contract();
        $contract->setType(Pricing::TYPE_NEGOTIATED);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);
        $deliveryPoint->setContract($contract);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2016-08-01'));
        $consumption->setIndexFinishedAt(new \DateTime('2017-07-31'));
        $consumption->setQuantity(100);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('2');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $turpeService = $this->createMock(TurpeService::class);
        $turpeService->expects($this->once())->method('getTurpeInterval')
            ->with('2', new \DateTime('2016-08-01'), new \DateTime('2017-07-31'), 100)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $analyzer = $this->getAnalyzerMock(TotalTaxAnalyzer::class, [$turpeService, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyIfDataDoesNotMatch()
    {
        $client = new Client();

        $contract = new Contract();
        $contract->setType(Pricing::TYPE_NEGOTIATED);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);
        $deliveryPoint->setContract($contract);

        $subscription = new InvoiceSubscription();
        $subscription->setTotal(6080*10**5); // 60,80€

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2020-03-14'));
        $consumption->setIndexFinishedAt(new \DateTime('2020-04-13'));
        $consumption->setQuantity(699);
        $consumption->setTotal(4760*10**5); // 47,60€

        $taxCta = new InvoiceTax();
        $taxCta->setType(InvoiceTax::TYPE_TAX_CTA);
        $taxCta->setTotal(1245*10**5); // 12,45€
        $taxCspe = new InvoiceTax();
        $taxCspe->setType(InvoiceTax::TYPE_TAX_CSPE);
        $taxCspe->setTotal(1573*10**5); // 15,73€
        $taxTdcfe = new InvoiceTax();
        $taxTdcfe->setType(InvoiceTax::TYPE_TAX_TDCFE);
        $taxTdcfe->setTotal(229*10**5); // 2,29€
        $taxTccfe = new InvoiceTax();
        $taxTccfe->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $taxTccfe->setTotal(323*10**5); // 3,23€

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$taxCta, $taxCspe, $taxTdcfe, $taxTccfe]));
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setAmountTVA(20000*10**5); // 200€

        $appliedRules = transInfo('total_tax_applied_rules_negotiated', [
            'consumption_ht' => 47.6,
            'consumption_tva' => 9.52,
            'total_taxes_tva' => 4.93,
            'total_min' => 17.6,
            'total_max' => 17.8,
            'margin' => 0.1,
            'tccfe_ht' => 3.23,
            'tdcfe_ht' => 2.29,
            'tcfe_ht' => 0,
            'cspe_ht' => 15.73,
            'cta_ht' => 12.45,
            'cta_tva' => 0.68,
            'cspe_tva' => 3.15,
            'tdcfe_tva' => 0.46,
            'tccfe_tva' => 0.65,
            'cg_ht' => 1.05,
            'cg_tva' => 0.06,
            'cc_ht' => 1.68,
            'cc_tva' => 0.09,
            'cs_fixed_ht' => 19.92,
            'cs_fixed_tva' => 1.1,
            'cs_variable_ht' => 10.0,
            'cs_variable_tva' => 2.0,
            'total_turpe_tva' => 3.24
        ]);

        $analyzer = $this->getAnalyzerMock(TotalTaxAnalyzer::class, [new TurpeService(), new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_AMOUNT,
            transInfo('amount_incorrect', ['amount_type' => 'TVA', 'type' => '']),
            $appliedRules, '200,00€', null, transInfo('expected_value_between_x_y', ['x' => '17,60€', 'y' => '17,80€']),
            'amount_tva', new AmountDiff(1822006087, 1023.63, Anomaly::PROFIT_PROVIDER)
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $client = new Client();

        $contract = new Contract();
        $contract->setType(Pricing::TYPE_REGULATED);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);
        $deliveryPoint->setContract($contract);

        $subscription = new InvoiceSubscription();
        $subscription->setTotal(6080*10**5); // 60,80€

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2020-03-14'));
        $consumption->setIndexFinishedAt(new \DateTime('2020-04-13'));
        $consumption->setQuantity(672);
        $consumption->setTotal(4760*10**5); // 47,60€

        $taxCta = new InvoiceTax();
        $taxCta->setType(InvoiceTax::TYPE_TAX_CTA);
        $taxCta->setTotal(1245*10**5); // 12,45€
        $taxCspe = new InvoiceTax();
        $taxCspe->setType(InvoiceTax::TYPE_TAX_CSPE);
        $taxCspe->setTotal(1573*10**5); // 15,73€
        $taxTdcfe = new InvoiceTax();
        $taxTdcfe->setType(InvoiceTax::TYPE_TAX_TDCFE);
        $taxTdcfe->setTotal(229*10**5); // 2,29€
        $taxTccfe = new InvoiceTax();
        $taxTccfe->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $taxTccfe->setTotal(323*10**5); // 3,23€

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$taxCta, $taxCspe, $taxTdcfe, $taxTccfe]));
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setAmountTVA(1781*10**5); // 17,81€

        $analyzer = $this->getAnalyzerMock(TotalTaxAnalyzer::class, [new TurpeService(), new AmountConversionService()]);
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->analyze($deliveryPointInvoice);
    }
}