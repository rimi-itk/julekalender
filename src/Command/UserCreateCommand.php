<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends UserCommand
{
    protected static $defaultName = 'user:create';

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($userRepository, $entityManager);
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('roles', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roles = $input->getArgument('roles');

        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword($this->passwordEncoder->encodePassword($user, $password))
            ->setRoles($roles);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('User %s created', $user->getEmail()));
        $this->showUser($user);

        return static::SUCCESS;
    }
}
