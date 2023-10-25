<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\TurpeService;
use App\Exceptions\IgnoreException;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group service
 * @group turpe-service
 */
class TurpeServiceTest extends WebTestCase
{
    public function testCanGetTurpeInterval(): void
    {
        $turpeService = new TurpeService();
        [$turpeMin, $turpeMax] = $turpeService->getTurpeInterval('2', new \DateTime('2019-11-01'), new \DateTime('2020-01-01'), 100);

        $this->assertEquals(212.58, round(floatval($turpeMin->getCg())/10**5, 2));
        $this->assertEquals(340.93, round(floatval($turpeMin->getCc())/10**5, 2));
        $this->assertEquals(20.26, round(floatval($turpeMin->getCsFixed())/10**7, 2));
        $this->assertEquals(143, round(floatval($turpeMin->getCsVariable())/10**5, 2));
    }

    public function testCanThrowExceptionIfNoTurpe(): void
    {
        $this->expectException(IgnoreException::class);

        $turpeService = new TurpeService();
        $turpeService->getTurpeInterval('2', new \DateTime('2016-11-01'), new \DateTime('2016-12-01'), 100);
    }
}