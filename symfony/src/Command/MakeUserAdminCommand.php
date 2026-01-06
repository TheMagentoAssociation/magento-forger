<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:make-user-admin',
    description: 'Makes a user that logged in via GitHub an admin locally',
)]
class MakeUserAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('User email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Please enter a valid email address.');
            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("User with email {$email} not found.");
            return Command::FAILURE;
        }

        if ($user->isAdmin()) {
            $io->warning("User {$email} is already an admin. No action taken.");
            return Command::SUCCESS;
        }

        try {
            $user->setIsAdmin(true);
            $this->entityManager->flush();

            $io->success("User {$email} is now an admin.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
