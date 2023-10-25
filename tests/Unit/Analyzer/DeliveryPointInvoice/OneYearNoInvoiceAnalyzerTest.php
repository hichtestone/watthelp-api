<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice;

use App\Analyzer\DeliveryPointInvoice\OneYearNoInvoiceAnalyzer;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\ClientManager;
use App\Manager\DeliveryPointManager;
use App\Manager\InvoiceManager;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Model\TranslationInfo;
use App\Query\Criteria;
use App\Tests\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-one-year-no-invoice
 */
class OneYearNoInvoiceAnalyzerTest extends WebTestCase
{
    public function testCanCatchAnomaly()
    {
        $deliveryPointManager = self::$container->get(DeliveryPointManager::class);
        $invoiceManager = self::$container->get(InvoiceManager::class);
        $deliveryPointInvoiceManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $clientManager = self::$container->get(ClientManager::class);

        $client = $clientManager->getByCriteria([new Criteria\Client\Id(1)]);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setClient($client);
        $deliveryPoint->setReference('DP099');
        $deliveryPoint->setCreatedAt(new \DateTime('2015-01-01'));
        $deliveryPoint->setUpdatedAt(new \DateTime('2015-01-01'));
        $deliveryPoint->setName('test');
        $deliveryPoint->setAddress('test');
        $deliveryPoint->setMeterReference('test');
        $deliveryPoint->setPower('4.74');
        $deliveryPointManager->insert($deliveryPoint);

        $previousInvoice = new Invoice();
        $previousInvoice->setClient($client);
        $previousInvoice->setReference('F01062016');
        $previousInvoice->setAmountHT(90);
        $previousInvoice->setAmountTVA(10);
        $previousInvoice->setAmountTTC(100);
        $previousInvoice->setEmittedAt(new \DateTime('2016-06-01'));
        $invoiceManager->insert($previousInvoice);

        $currentInvoice = new Invoice();
        $currentInvoice->setClient($client);
        $currentInvoice->setReference('F08012018');
        $currentInvoice->setAmountHT(90);
        $currentInvoice->setAmountTVA(10);
        $currentInvoice->setAmountTTC(100);
        $currentInvoice->setEmittedAt(new \DateTime('2018-01-08'));
        $invoiceManager->insert($currentInvoice);

        $previousYearDPInvoice = new DeliveryPointInvoice();
        $previousYearDPInvoice->setDeliveryPoint($deliveryPoint);
        $previousYearDPInvoice->setInvoice($previousInvoice);
        $previousYearDPInvoice->setType(DeliveryPointInvoice::TYPE_REAL);
        $previousYearDPInvoice->setAmountHT(90);
        $previousYearDPInvoice->setAmountTVA(10);
        $previousYearDPInvoice->setAmountTTC(100);
        $previousYearDPInvoice->setPowerSubscribed('47.7');
        $deliveryPointInvoiceManager->insert($previousYearDPInvoice);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setInvoice($currentInvoice);
        $deliveryPointInvoice->setType(DeliveryPointInvoice::TYPE_REAL);
        $deliveryPointInvoice->setAmountHT(90);
        $deliveryPointInvoice->setAmountTVA(10);
        $deliveryPointInvoice->setAmountTTC(100);
        $deliveryPointInvoice->setPowerSubscribed('47.7');
        $deliveryPointInvoiceManager->insert($deliveryPointInvoice);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previousYearDPInvoice);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(OneYearNoInvoiceAnalyzer::class, [$deliveryPointInvoiceManager]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_DATE,
            transInfo('no_delivery_point_invoice_emitted_for_more_than_a_year'),
            transInfo('one_year_no_invoice_applied_rules', [
                'from' => new \DateTime('2017-01-08'),
                'to' => new \DateTime('2018-01-08')
            ])
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCannotCatchAnomalyIfDataIsCorrect()
    {
        $deliveryPointInvoiceManager = self::$container->get(DeliveryPointInvoiceManager::class);

        /* emittedAt : 2018-03-01 */
        $previousYearDPInvoice = $deliveryPointInvoiceManager->findBy([
            'id' => 2,
        ])[0];

        /* emittedAt : 2018-09-01 */
        $deliveryPointInvoice = $deliveryPointInvoiceManager->findBy([
            'id' => 3,
        ])[0];
        $dpia = $deliveryPointInvoice->getDeliveryPointInvoiceAnalysis() ?? new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previousYearDPInvoice);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(OneYearNoInvoiceAnalyzer::class, [$deliveryPointInvoiceManager], ['getPreviousYear']);
        $analyzer->expects($this->once())->method('getPreviousYear')->with($deliveryPointInvoice)->willReturn($previousYearDPInvoice);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}