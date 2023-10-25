<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Invoice\InvoiceConsumption;
use App\Exceptions\IgnoreException;
use App\Manager\ClientManager;
use App\Manager\TaxManager;
use App\Query\Criteria;
use App\Service\TaxService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group service
 * @group tax-service
 */
class TaxServiceTest extends WebTestCase
{

    public function testCanGetTaxAmountInterval()
    {
        $clientManager = self::$container->get(ClientManager::class);
        $taxManager = self::$container->get(TaxManager::class);
        $service = new TaxService($taxManager);

        $client = $clientManager->getByCriteria([new Criteria\Client\Id(1)]);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2016-01-01'));
        $consumption->setIndexFinishedAt(new \DateTime('2020-01-01'));

        $result = $service->getTaxAmounts($consumption, $client);

        $this->assertEquals([
            'cspe' => [140, 2112],
            'tdcfe' => [4654,4654],
            'tccfe' => [489,1107],
            'cta' => [2704,2704]
        ], $result);
    }

    public function testCanThrowExceptionIfInvalidConsumption()
    {
        $this->expectException(IgnoreException::class);
        
        $clientManager = self::$container->get(ClientManager::class);
        $taxManager = self::$container->get(TaxManager::class);
        $service = new TaxService($taxManager);

        $client = $clientManager->getByCriteria([new Criteria\Client\Id(1)]);

        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2016-01-01'));

        $service->getTaxAmounts($consumption, $client);
    }

}