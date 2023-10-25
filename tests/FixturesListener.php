<?php

declare(strict_types=1);

namespace App\Tests;

use Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestSuite;
use App\Tests\Logger\Profiler\Db;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestListener;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\HttpKernel\Kernel;
use PHPUnit\Framework\AssertionFailedError;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

class FixturesListener implements TestListener
{
    protected static bool $init = false;
    protected Db $profiler;

    public function addError(Test $test, \Throwable $e, float $time): void
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(Test $test, \Throwable $e, float $time): void
    {
    }

    public function addRiskyTest(Test $test, \Throwable $e, float $time): void
    {
    }

    public function addSkippedTest(Test $test, \Throwable $e, float $time): void
    {
    }

    /**
     * @throws Exception
     */
    public function startTest(Test $test): void
    {
        if (!method_exists($test, 'getKernel')) {
            return;
        }

        /** @var Kernel $kernel */
        $kernel = $test->getKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->profiler = $container->get(Db::class);
        $this->profiler->enable(true);

        if (!$test instanceof FunctionalWebTestCase) {
            return;
        }

        // Prepare test db.
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $host = $entityManager->getConnection()->getHost();
        $user = $entityManager->getConnection()->getUsername();
        $password = $entityManager->getConnection()->getPassword() ? '-p'.$entityManager->getConnection()->getPassword() : '';
        $name = $entityManager->getConnection()->getDatabase();

        if (false === self::$init) {
            $entityManager->getConnection()->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', $name));

            try {
                // Drop & Create schema
                $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
                $schemaTool = new SchemaTool($entityManager);
                $schemaTool->dropSchema($metadatas);
                $schemaTool->createSchema($metadatas);

                // Load fixtures
                $loader = new Loader();
                $loader->loadFromDirectory(__DIR__.'/../src/DataFixtures/');
                $purger = new ORMPurger($entityManager);
                $executor = new ORMExecutor($entityManager, $purger);
                $executor->execute($loader->getFixtures());

                $entityManager->getConnection()->exec(sprintf('drop database if exists `%s`', $name.'_copy'));
                $entityManager->getConnection()->exec(sprintf('create database `%s`', $name.'_copy'));
            } catch (\Throwable $t) {
                var_dump($t->getMessage());
                die;
            }

            exec(sprintf(
                'mysqldump -h %s -u %s %s %s | mysql -h %s -u %s %s %s',
                $host,
                $user,
                $password,
                $name,
                $host,
                $user,
                $password,
                $name.'_copy'
            ));
            self::$init = true;
        }
    }

    /**
     * @throws Exception
     */
    public function endTest(Test $test, float $time): void
    {
        if (!method_exists($test, 'getKernel')) {
            return;
        }

        if (null === $this->profiler) {
            return;
        }

        if ($this->profiler->hasTheDbBeenUpdated()) {
            /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
            $kernel = $test->getKernel();
            $kernel->boot();
            $container = $kernel->getContainer();

            /** @var EntityManager $entityManager */
            $entityManager = $container->get('doctrine.orm.entity_manager');

            $host = $entityManager->getConnection()->getHost();
            $user = $entityManager->getConnection()->getUsername();
            $password = $entityManager->getConnection()->getPassword() ? '-p'.$entityManager->getConnection()->getPassword() : '';
            $name = $entityManager->getConnection()->getDatabase();

            $entityManager->getConnection()->exec(sprintf('drop database if exists `%s`', $name));
            $entityManager->getConnection()->exec(sprintf('create database `%s`', $name));

            exec(sprintf(
                'mysqldump -h %s -u %s %s %s | mysql -h %s -u %s %s %s',
                $host,
                $user,
                $password,
                $name.'_copy',
                $host,
                $user,
                $password,
                $name
            ));

            $kernel->shutdown();
        }
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }
}
