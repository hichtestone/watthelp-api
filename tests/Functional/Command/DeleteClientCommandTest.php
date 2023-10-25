<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\FunctionalWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 * @group command
 * @group command-delete-client
 */
class DeleteClientCommandTest extends FunctionalWebTestCase
{

    /**
     * @dataProvider getDataProvider
     */
    public function testCanDeleteClient(int $id, string $expected): void
    {
        $application = new Application($this->getKernel());

        $command = $application->find('app:delete-client');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['clientId' => $id]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testCannotDeleteClient(int $id, string $expected): void
    {
        $application = new Application($this->getKernel());

        $command = $application->find('app:delete-client');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['clientId' => $id]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expected, $output);
    }
}