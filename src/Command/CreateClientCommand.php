<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Client;
use App\Entity\User;
use App\Manager\ClientManager;
use App\Manager\FileManager;
use App\Manager\UserManager;
use App\Query\Criteria\User\Email;
use App\Service\S3Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CreateClientCommand extends Command
{
    protected static $defaultName = 'app:create-client';
    private SerializerInterface $serializer;
    private ClientManager $clientManager;
    private UserManager $userManager;
    private FileManager $fileManager;
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $encoder;
    private S3Uploader $uploader;

    public function __construct(
        SerializerInterface $serializer,
        ClientManager $clientManager,
        UserManager $userManager,
        FileManager $fileManager,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder,
        S3Uploader $uploader,
        ?string $name = null
    ) {
        $this->serializer = $serializer;
        $this->clientManager = $clientManager;
        $this->userManager = $userManager;
        $this->fileManager = $fileManager;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->uploader = $uploader;

        parent::__construct($name);
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setDescription('Creates a new client and a new user attached to it')
            ->addArgument('clientName', InputArgument::REQUIRED, 'The client name')
            ->addArgument('clientLanguage', InputArgument::REQUIRED, 'The client language')
            ->addArgument('userEmail', InputArgument::REQUIRED, 'The user email')
            ->addArgument('userPassword', InputArgument::REQUIRED, 'The user password')
            ->addArgument('userFirstname', InputArgument::REQUIRED, 'The user first name')
            ->addArgument('userLastname', InputArgument::REQUIRED, 'The user last name')
            ->addOption('clientCity', null, InputOption::VALUE_REQUIRED, 'The client city', null)
            ->addOption('clientZipCode', null, InputOption::VALUE_REQUIRED, 'The city zipcode', null)
            ->addOption('clientDescription', null, InputOption::VALUE_REQUIRED, 'The client description', null)
            ->addOption('clientAddress', null, InputOption::VALUE_REQUIRED, 'The client address', null)
            ->addOption('clientDepartment', null, InputOption::VALUE_REQUIRED, 'The city department', null)
            ->addOption('clientInsee', null, InputOption::VALUE_REQUIRED, 'The city INSEE code', null)
            ->addOption('clientLogo', null, InputOption::VALUE_REQUIRED, 'The city logo')
            ->addOption('userLanguage', null, InputOption::VALUE_REQUIRED, 'The user language - defaults to client language if not specified');
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clientData = [
            'name' => $input->getArgument('clientName'),
            'defaultLanguage' => $input->getArgument('clientLanguage'),
            'city' => $input->getOption('clientCity'),
            'zipcode' => $input->getOption('clientZipCode'),
            'description' => $input->getOption('clientDescription'),
            'address' => $input->getOption('clientAddress'),
            'department' => $input->getOption('clientDepartment'),
            'insee' => $input->getOption('clientInsee')
        ];
        $logoPath = $input->getOption('clientLogo');
        $userData = [
            'email' => $input->getArgument('userEmail'),
            'password' => $input->getArgument('userPassword'),
            'first_name' => $input->getArgument('userFirstname'),
            'last_name' => $input->getArgument('userLastname'),
            'language' => $input->getOption('userLanguage') ?? $input->getArgument('clientLanguage')
        ];

        $user = $this->userManager->getByCriteria(null, [new Email($userData['email'])]);
        if ($user !== null) {
            $output->writeln("A client with the mail address {$userData['email']} already exists.");
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();

        $client = $this->serializer->denormalize($clientData, Client::class);
        $this->clientManager->insert($client);

        $user = $this->serializer->denormalize($userData, User::class);
        $user->setSuperAdmin(true);
        $client->addUser($user);
        $this->userManager->insert($user);

        if ($logoPath) {
            $localFile = sys_get_temp_dir() . '/tmp_logo_' . uniqid();
            if (!copy($logoPath, $localFile)) {
                $this->entityManager->getConnection()->rollBack();
                $output->writeln("Could not copy file {$logoPath}.");
                return Command::FAILURE;
            }
            try {
                $logo = $this->uploader->uploadFile(new File($localFile), $client);
                $logo->setUser($user);
                $this->fileManager->insert($logo);
                $client->setLogo($logo);
            } finally {
                unlink($localFile);
            }
        }

        $this->clientManager->update($client);

        $clientName = $client->getName();
        $output->writeln("The client $clientName has been created successfully.");

        $this->entityManager->getConnection()->commit();

        return Command::SUCCESS;
    }
}