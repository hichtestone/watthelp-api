<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\Manager\UserManager;
use App\Query\Criteria;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Serializer\SerializerInterface;

abstract class FunctionalWebTestCase extends WebTestCase
{
    protected HttpKernel $httpKernel;
    protected EntityManagerInterface $entityManager;
    protected SerializerInterface $serializer;

    private JWTTokenManagerInterface $jwtTokenManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->getClientInstance();
        $this->serializer = self::$container->get(SerializerInterface::class);
        $this->entityManager = self::$container->get(EntityManagerInterface::class);
        $this->jwtTokenManager = self::$container->get(JWTTokenManagerInterface::class);
    }

    /**
     * Close doctrine connections to avoid having a 'too many connections'
     * message when running many tests
     * Source: http://sf.khepin.com/2012/02/symfony2-testing-with-php-unit-quick-tip/.
     */
    public function tearDown(): void
    {
        self::$container->get('doctrine')->getConnection()->close();
        parent::tearDown();
    }

    /**
     * Connect an user and add Authorisation on the request.
     *
     * @throws NonUniqueResultException
     */
    public function connectUser(string $userEmail): void
    {
        $user = $this->getUser($userEmail);
        $this->addAuthorization($user);
    }

    public function addAuthorization(User $user): void
    {
        $token = $this->jwtTokenManager->create($user);
        $this->getClientInstance()->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
    }

    protected function getUser(string $userEmail): User
    {
        $userManager = self::$container->get(UserManager::class);
        return $userManager->getByCriteria(null, [new Criteria\User\Email($userEmail)]);
    }

    /**
     * Add expand data on request.
     */
    public function setExpandData(string $expand): void
    {
        $this->getClientInstance()->setServerParameter('HTTP_X-Expand-Data', $expand);
    }

    public function listTest(string $url, string $email, array $query, array $expected): void
    {
        $this->connectUser($email);
        $this->client->request('GET', \sprintf('%s?%s', trim($url, '/'), http_build_query($query)));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $response = $this->client->getResponse()->getContent();

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    public function endpointTest(string $url, string $method, string $user, array $post, array $expected, string $expand = null): void
    {
        $this->connectUser($user);

        if ($expand) {
            $this->setExpandData($expand);
        }

        $this->client->request($method, $url, [], [], [], \json_encode($post));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $response = $this->client->getResponse()->getContent();
        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    public function handleErrorTest(string $url, string $method, string $user, array $post, int $expectedStatusCode, array $expectedErrors): void
    {
        $this->connectUser($user);
        $this->client->request($method, $url, [], [], [], \json_encode($post));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedErrors), $this->client->getResponse()->getContent(), $this->client->getResponse()->getContent());
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getSheet(string $filePath): array
    {
        try {
            $localFile = sys_get_temp_dir() . '/tmp_file';
            
            if (!copy($filePath, $localFile)) {
                $this->fail('Impossible de copier le fichier.');
            }
            $sheet = (new SpreadsheetService())->makeXslxSheet($localFile);
            return $sheet->toArray();
        } finally {
            unlink($localFile);
        }
    }
}