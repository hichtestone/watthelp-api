<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Subscription;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Subscription\SubscriptionExceedsConsumptionAnalyzer;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Invoice\InvoiceSubscription;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-subscription
 * @group analyzer-subscription-exceeds-consumption
 */
class SubscriptionExceedsConsumptionAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);

        $analyzer = new SubscriptionExceedsConsumptionAnalyzer($translationManager, $logger, new AmountConversionService());

        $this->assertEquals('delivery_point_invoice.subscription.subscription_exceeds_consumption', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);

        $analyzer = new SubscriptionExceedsConsumptionAnalyzer($translationManager, $logger, new AmountConversionService());

        $this->assertEquals(AnalyzerInterface::GROUP_SUBSCRIPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveSubscriptionTotal()
    {
        $subscription = new InvoiceSubscription();
        $consumption = new InvoiceConsumption();
        $consumption->setTotal(127*10**7);
        $dpi = new DeliveryPointInvoice();
        $dpi->setSubscription($subscription);
        $dpi->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(SubscriptionExceedsConsumptionAnalyzer::class, [new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('ht_amount_missing', ['type' => 'subscription']), 'subscription.total');
        $analyzer->analyze($dpi);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveConsumptionTotal()
    {
        $subscription = new InvoiceSubscription();
        $subscription->setTotal(127*10**7);
        $consumption = new InvoiceConsumption();
        $dpi = new DeliveryPointInvoice();
        $dpi->setSubscription($subscription);
        $dpi->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(SubscriptionExceedsConsumptionAnalyzer::class, [new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('ht_amount_missing', ['type' => 'consumption']), 'consumption.total');
        $analyzer->analyze($dpi);
    }

    public function testCanThrowAnomalyEventIfSubscriptionTotalExceedsConsumptionTotal()
    {
        $subscription = new InvoiceSubscription();
        $subscription->setTotal(127*10**7); // 127€
        $consumption = new InvoiceConsumption();
        $consumption->setTotal(11754*10**5); // 117,54€
        $dpi = new DeliveryPointInvoice();
        $dpi->setSubscription($subscription);
        $dpi->setConsumption($consumption);

        $anomalyDescription = transInfo(
            'subscription_amount_exceeds_consumption_amount',
            [
                'subscription_amount' => '127,00€',
                'consumption_total' => '117,54€'
            ]
        );
        $analyzer = $this->getAnalyzerMock(SubscriptionExceedsConsumptionAnalyzer::class, [new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_AMOUNT,
            $anomalyDescription,
            $anomalyDescription,
            '127,00€', null, transInfo('expected_value_inferior_to', ['expected_value' => '117,54€']),
            'subscription.total', new AmountDiff(94600000, 8.05)
        );
        $analyzer->analyze($dpi);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $subscription = new InvoiceSubscription();
        $subscription->setTotal(27*10**7); // 27€
        $consumption = new InvoiceConsumption();
        $consumption->setTotal(11754*10**5); // 117,54€
        $dpi = new DeliveryPointInvoice();
        $dpi->setSubscription($subscription);
        $dpi->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(SubscriptionExceedsConsumptionAnalyzer::class, [new AmountConversionService()]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($dpi);
    }
}