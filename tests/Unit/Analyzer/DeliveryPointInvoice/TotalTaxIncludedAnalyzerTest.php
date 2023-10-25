<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\TotalTaxIncludedAnalyzer;
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
 * @group analyzer-total-tax-included
 */
class TotalTaxIncludedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $turpeService = $this->createMock(TurpeService::class);

        $analyzer = new TotalTaxIncludedAnalyzer($translationManager, $logger, $turpeService, new AmountConversionService());

        $this->assertEquals('delivery_point_invoice.total_tax_included', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $turpeService = $this->createMock(TurpeService::class);

        $analyzer = new TotalTaxIncludedAnalyzer($translationManager, $logger, $turpeService, new AmountConversionService());

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
        $analyzer = $this->getAnalyzerMock(TotalTaxIncludedAnalyzer::class, [$turpeService, new AmountConversionService()]);
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
        $deliveryPointInvoice->setAmountTTC(20000*10**5); // 200€

        $turpeService = $this->createMock(TurpeService::class);
        $turpeService->expects($this->once())->method('getTurpeInterval')
            ->with('2', new \DateTime('2016-08-01'), new \DateTime('2017-07-31'), 100)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $analyzer = $this->getAnalyzerMock(TotalTaxIncludedAnalyzer::class, [$turpeService, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyIfDataDoesNotMatch()
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
        $deliveryPointInvoice->setAmountTTC(20000*10**5); // 200€

        $appliedRules = transInfo('total_tax_included_applied_rules_regulated', [
            'consumption_ht' => 47.6,
            'consumption_ttc' => 57.12,
            'total_taxes_ttc' => 38.63,
            'total_min' => 159.8,
            'total_max' => 160.0,
            'margin' => 0.1,
            'tccfe_ht' => 3.23,
            'tdcfe_ht' => 2.29,
            'tcfe_ht' => 0,
            'cspe_ht' => 15.73,
            'cta_ht' => 12.45,
            'cta_ttc' => 13.13,
            'cspe_ttc' => 18.88,
            'tdcfe_ttc' => 2.75,
            'tccfe_ttc' => 3.88,
            'subscription_ht' => 60.8,
            'subscription_ttc' => 64.14
        ]);
        
        $analyzer = $this->getAnalyzerMock(TotalTaxIncludedAnalyzer::class, [new TurpeService(), new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_AMOUNT,
            transInfo('amount_incorrect', ['amount_type' => 'TTC', 'type' => '']),            
            $appliedRules, '200,00€', null, transInfo('expected_value_between_x_y', ['x' => '159,80€', 'y' => '160,00€']),
            'amount_ttc', new AmountDiff(400012500, 25.0, Anomaly::PROFIT_PROVIDER)
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
        $consumption->setTotal(4760*10**5); // 46,60€

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
        $deliveryPointInvoice->setAmountTTC(15991*10**5); // 159.91€

        $analyzer = $this->getAnalyzerMock(TotalTaxIncludedAnalyzer::class, [new TurpeService(), new AmountConversionService()]);
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->analyze($deliveryPointInvoice);
    }
}