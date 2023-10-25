<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\FunctionalWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 * @group command
 * @group command-create-client
 */
class CreateClientCommandTest extends FunctionalWebTestCase
{

    /**
     * @dataProvider getDataProvider
     */
    public function testCanCreateClient(array $arguments, array $options, string $expected): void
    {
        $application = new Application($this->getKernel());

        $command = $application->find('app:create-client');
        $commandTester = new CommandTester($command);

        $commandTester->execute($arguments, $options);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testCannotCreateClient(array $arguments, array $options, string $expected): void
    {
        $application = new Application($this->getKernel());

        $command = $application->find('app:create-client');
        $commandTester = new CommandTester($command);

        $commandTester->execute($arguments, $options);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expected, $output);
    }
}