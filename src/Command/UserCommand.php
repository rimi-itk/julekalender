<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class UserCommand extends Command
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct(null);
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    protected function getUser(string $email)
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new RuntimeException(sprintf('Cannot find user %s', $email));
        }

        return $user;
    }

    protected function getUsers()
    {
        return $this->userRepository->findAll();
    }

    protected function showUsers(array $users)
    {
        $table = new Table($this->output);
        $table->setHeaders([
            'email',
            'roles',
        ]);
        foreach ($users as $user) {
            $table->addRow([
                $user->getEmail(),
                implode(', ', $user->getRoles()),
            ]);
        }
        $table->render();
    }

    protected function showUser(User $user)
    {
        return $this->showUsers([$user]);
    }
}
