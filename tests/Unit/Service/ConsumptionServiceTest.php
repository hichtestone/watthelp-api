<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Invoice\InvoiceConsumption;
use App\Exceptions\IgnoreException;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\Invoice\InvoiceConsumptionManager;
use App\Manager\Invoice\InvoiceTaxManager;
use App\Service\ConsumptionService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group service
 * @group consumption-service
 */
class ConsumptionServiceTest extends WebTestCase
{
    public function testCanGetConsumptionIntervalDate()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStart(99500);
        $consumption->setIndexFinish(1000);
        $consumption->setQuantity(1500);

        $invoiceConsumptionManager = self::$container->get(InvoiceConsumptionManager::class);
        $deliveryPointInvoiceManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $invoiceTaxManager = self::$container->get(InvoiceTaxManager::class);
        $service = new ConsumptionService($invoiceConsumptionManager, $deliveryPointInvoiceManager, $invoiceTaxManager);
        $result = $service->getConsumptionQuantity($consumption);

        $this->assertEquals(1500, $result);
    }

    public function testCanThrowExceptionIfInvalidConsumption()
    {
        $this->expectException(IgnoreException::class);
        
        $consumption = new InvoiceConsumption();

        $invoiceConsumptionManager = self::$container->get(InvoiceConsumptionManager::class);
        $deliveryPointInvoiceManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $invoiceTaxManager = self::$container->get(InvoiceTaxManager::class);
        $service = new ConsumptionService($invoiceConsumptionManager, $deliveryPointInvoiceManager, $invoiceTaxManager);
        $service->getConsumptionQuantity($consumption);
    }
}
