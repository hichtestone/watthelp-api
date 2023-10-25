<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer;

use App\Analyzer\AnalyzerChain;
use App\Tests\WebTestCase;

class AnalyzerChainTest extends WebTestCase
{
    /**
     * @group unit
     * @group analyzer
     * @group analyzer-chain
     */
    public function testAllAnalyzersAreCalled()
    {
        $chainAnalyzer = self::$container->get(AnalyzerChain::class);

        $analyzers = $chainAnalyzer->getAnalyzers();
        $this->assertCount(2, $analyzers);

        $this->assertCount(11, $analyzers[1]); // priority 1
        $this->assertCount(11, $analyzers[2]); // Priority 2
    }
}