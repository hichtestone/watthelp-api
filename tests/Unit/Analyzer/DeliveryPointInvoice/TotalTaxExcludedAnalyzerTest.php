<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\TotalTaxExcludedAnalyzer;
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
 * @group analyzer-total-tax-excluded
 */
class TotalTaxExcludedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $turpeService = $this->createMock(TurpeService::class);

        $analyzer = new TotalTaxExcludedAnalyzer($translationManager, $logger, $turpeService, new AmountConversionService());

        $this->assertEquals('delivery_point_invoice.total_tax_excluded', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $turpeService = $this->createMock(TurpeService::class);

        $analyzer = new TotalTaxExcludedAnalyzer($translationManager, $logger, $turpeService, new AmountConversionService());

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
        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$turpeService, new AmountConversionService()]);
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

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$turpeService, new AmountConversionService()]);
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
        $consumption->setQuantity(672);
        $consumption->setTotal(4760*10**5); // 46,60€

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);
        $tax->setTotal(3370*10**5); // 33,70€

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setAmountHT(20000*10**5); // 200€

        $appliedRules = transInfo('total_tax_excluded_applied_rules_regulated', [
            'consumption' => 47.6,
            'total_taxes' => 33.7,
            'total_min' => 142.0,
            'total_max' => 142.2,
            'margin' => 0.1,
            'tccfe' => 0,
            'tdcfe' => 0,
            'tcfe' => 0,
            'cspe' => 0,
            'cta' => 33.7,
            'subscription' => 60.8
        ]);

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [new TurpeService(), new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_AMOUNT,
            transInfo('amount_incorrect', ['amount_type' => 'HT', 'type' => '']),            
            $appliedRules, '200,00€', null, transInfo('expected_value_between_x_y', ['x' => '142,00€', 'y' => '142,20€']),
            'amount_ht', new AmountDiff(578000000, 40.65, Anomaly::PROFIT_PROVIDER)
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

        $tax = new InvoiceTax();
        $tax->setType(InvoiceTax::TYPE_TAX_CTA);
        $tax->setTotal(3370*10**5); // 33,70€

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setTaxes(new ArrayCollection([$tax]));
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setAmountHT(14210*10**5); // 142.10€

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [new TurpeService(), new AmountConversionService()]);
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->analyze($deliveryPointInvoice);
    }
}