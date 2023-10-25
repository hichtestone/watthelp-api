<?php

declare(strict_types=1);

namespace App\Tests;

use App\Manager\TranslationManager;
use App\Service\LogService;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    use PHPMatcherAssertions;

    protected KernelBrowser $client;

    /**
     * @param bool $reset
     *
     * @return KernelBrowser
     */
    public function getClientInstance(array $options = [], array $server = [], $reset = false): KernelBrowser
    {
        if (!isset($this->client) && false === $reset) {
            $this->client = self::createClient($options, $server);
        }

        return $this->client;
    }

    /**
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface
    {
        $client = $this->getClientInstance();

        $kernel = $client->getKernel();
        $kernel->boot();

        return $kernel;
    }

    /**
     * @throws \ReflectionException
     */
    public function getDataProvider(string $methodName): array
    {
        $classInfo = new ReflectionClass($this);
        $path = dirname($classInfo->getFileName());
        $path = "$path/provider/{$classInfo->getShortName()}/$methodName";
        $extensions = ['json', 'php'];
        $temporaryData = null;
        $fileFound = false;

        foreach ($extensions as $extension) {
            $dataFile = "$path.$extension";
            if (!file_exists($dataFile)) {
                continue;
            }

            $fileFound = true;

            switch ($extension) {
                case 'json':
                    $content = file_get_contents($dataFile);
                    $temporaryData = \json_decode($content, true);
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new \RuntimeException("JSON file $dataFile is invalid. ".json_last_error_msg());
                    }
                    break;

                case 'php':
                    $temporaryData = include $dataFile;
                    break;
            }

            if (!is_array($temporaryData)) {
                throw new \RuntimeException('Content is not a valid array.');
            }

            $data = [];
            $i = 1;
            foreach ($temporaryData as $key => $item) {
                $data["#$i $key"] = $item;
                ++$i;
            }
        }

        if (!isset($fileFound)) {
            throw new \RuntimeException("Unable to find test file in path: $path.");
        }

        if (!isset($data)) {
            throw new \RuntimeException('No data set.');
        }

        return $data;
    }

    protected function getAnalyzerMock(string $analyzer, array $constructorArgs = [], array $additionalMethods = []): object
    {
        return $this->getMockBuilder($analyzer)
            ->setMethods(array_merge(['ignore', 'anomaly'], $additionalMethods))
            ->setConstructorArgs(array_merge(
                [self::$container->get(TranslationManager::class), self::$container->get(LogService::class)],
                $constructorArgs)
            )
            ->getMock();
    }
}