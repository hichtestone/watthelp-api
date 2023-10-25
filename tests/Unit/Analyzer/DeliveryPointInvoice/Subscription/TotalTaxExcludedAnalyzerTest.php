<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Subscription;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Subscription\TotalTaxExcludedAnalyzer;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceSubscription;
use App\Entity\Pricing;
use App\Manager\PricingManager;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\DateFormatService;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-subscription
 * @group analyzer-subscription-total-tax-excluded
 */
class TotalTaxExcludedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(PricingManager::class);

        $analyzer = new TotalTaxExcludedAnalyzer($translationManager, $logger, $manager, new AmountConversionService());

        $this->assertEquals('delivery_point_invoice.subscription.total_tax', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(PricingManager::class);

        $analyzer = new TotalTaxExcludedAnalyzer($translationManager, $logger, $manager, new AmountConversionService());

        $this->assertEquals(AnalyzerInterface::GROUP_SUBSCRIPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveSubscriptionTotal()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $subscription = new InvoiceSubscription();
        $deliveryPointInvoice->setSubscription($subscription);

        $manager = $this->createMock(PricingManager::class);
        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('ht_amount_missing', ['type' => 'subscription']), 'subscription.total');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveStartedAt()
    {
        $subscription = new InvoiceSubscription();
        $subscription->setTotal(96);
        $subscription->setFinishedAt(new \DateTime());

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setSubscription($subscription);

        $manager = $this->createMock(PricingManager::class);
        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('started_at_missing', ['type' => 'subscription']), 'subscription.started_at');
        $analyzer->analyze($deliveryPointInvoice);
    }
    
    public function testCanThrowIgnoreEventIfWeDontHaveFinishedAt()
    {
        $subscription = new InvoiceSubscription();
        $subscription->setTotal(96);
        $subscription->setStartedAt(new \DateTime());

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setSubscription($subscription);

        $manager = $this->createMock(PricingManager::class);
        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('finished_at_missing', ['type' => 'subscription']), 'subscription.finished_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHavePowerSubscribed()
    {
        $subscription = new InvoiceSubscription();
        $subscription->setTotal(96);
        $subscription->setStartedAt(new \DateTime());
        $subscription->setFinishedAt(new \DateTime());

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setSubscription($subscription);

        $manager = $this->createMock(PricingManager::class);
        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('power_subscribed_missing'), 'power_subscribed');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveRates()
    {
        $deliveryPoint = new DeliveryPoint();

        $subscription = new InvoiceSubscription();
        $subscription->setTotal(96);
        $subscription->setStartedAt($startedAt = new \DateTime('2018-07-01'));
        $subscription->setFinishedAt($finishedAt = new \DateTime('2018-09-30'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4.9');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setSubscription($subscription);

        $manager = $this->createMock(PricingManager::class);
        $manager->expects($this->once())
            ->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $startedAt, $finishedAt)
            ->willReturn([]);

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('no_pricing_found_in_period', ['from' => $startedAt->format(DateFormatService::ANALYZER), 'to' => $finishedAt->format(DateFormatService::ANALYZER)]));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEvent()
    {
        $deliveryPoint = new DeliveryPoint();

        $subscription = new InvoiceSubscription();
        $subscription->setTotal($totalAmount = 8448 * 10**5); // 84,48€
        $subscription->setStartedAt($startedAt = new \DateTime('2018-07-01'));
        $subscription->setFinishedAt($finishedAt = new \DateTime('2018-09-31'));

        $contract = new Contract();
        $contract->setInvoicePeriod('2');

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setContract($contract);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4.9');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);

        $pricing = new Pricing();
        $pricing->setSubscriptionPrice(760*10**5); // 7.60€/kW/month

        $manager = $this->createMock(PricingManager::class);
        $manager->expects($this->once())
            ->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $startedAt, $finishedAt)
            ->willReturn([$pricing]);

        $appliedRules = transInfo('subscription_total_tax_excluded_applied_rules', [
            'from' => $startedAt,
            'to' => $finishedAt,
            'minimum_base_price' => 74.47,
            'maximum_base_price' => 74.49,
            'period' => 2,
            'power_subscribed' => 4.9,
            'margin' => 0.01,
            'minimum' => 74.47,
            'maximum' => 74.49
        ]);
        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Invoice\Anomaly::TYPE_AMOUNT,
            transInfo('amount_incorrect', ['amount_type' => 'HT', 'type' => 'subscription']),
            $appliedRules, '84,48€', null,
            transInfo('expected_value_between_x_y', ['x' => '74,47€', 'y' => '74,49€']),
            'subscription.total', new AmountDiff(99900000, 13.41, Anomaly::PROFIT_PROVIDER)
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanAnalyzeWithoutAnomaly()
    {
        $deliveryPoint = new DeliveryPoint();

        $subscription = new InvoiceSubscription();
        $subscription->setTotal($totalAmount = 7448 * 10**5); // 74,48€
        $subscription->setStartedAt($startedAt = new \DateTime('2018-07-01'));
        $subscription->setFinishedAt($finishedAt = new \DateTime('2018-09-31'));

        $contract = new Contract();
        $contract->setInvoicePeriod('2');

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setContract($contract);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('4.9');
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);

        $pricing = new Pricing();
        $pricing->setSubscriptionPrice(760*10**5); // 7.60€/kW/month

        $manager = $this->createMock(PricingManager::class);
        $manager->expects($this->once())
            ->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $startedAt, $finishedAt)
            ->willReturn([$pricing]);

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$manager, new AmountConversionService()]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}