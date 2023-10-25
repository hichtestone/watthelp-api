<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Client;
use App\Entity\User;
use App\Manager\ClientManager;
use App\Query\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteClientCommand extends Command
{
    protected static $defaultName = 'app:delete-client';
    private SerializerInterface $serializer;
    private ClientManager $clientManager;

    public function __construct(
        SerializerInterface $serializer,
        ClientManager $clientManager,
        ?string $name = null
    ) {
        $this->serializer = $serializer;
        $this->clientManager = $clientManager;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Deletes a client and users linked to it')
            ->addArgument('clientId', InputArgument::REQUIRED, 'The client id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clientId = $input->getArgument('clientId');

        $client = $this->clientManager->getByCriteria([new Criteria\Client\Id($clientId)]);
        if ($client === null) {
            $output->writeln("Could not find a client with the id $clientId");
            return Command::FAILURE;
        }

        $this->clientManager->delete($client);

        $client = $this->clientManager->getByCriteria([new Criteria\Client\Id($clientId)]);
        
        if ($client !== null) {
            $output->writeln("Could not delete the client $clientId");
            return Command::FAILURE;
        }

        $output->writeln("The client $clientId has been deleted successfully.");

        return Command::SUCCESS;
    }
}