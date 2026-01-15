<?php

namespace App\Command;

use App\Entity\Patient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Create a doctor and a patient user',
)]
class CreateUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userRepo = $this->entityManager->getRepository(User::class);
        $patientRepo = $this->entityManager->getRepository(Patient::class);

        // 👨‍⚕️ Create Doctor
        $doctor = $userRepo->findOneBy(['email' => 'doctor@hospital.com']);
        if (!$doctor) {
            $doctor = new User();
            $doctor->setEmail('doctor@hospital.com');
            $doctor->setNom('Doctor Demo');
            $doctor->setRoles(['ROLE_DOCTOR']);
            $doctorPassword = $this->passwordHasher->hashPassword($doctor, 'doctor123');
            $doctor->setPassword($doctorPassword);
            $this->entityManager->persist($doctor);
        }

        // 🧑‍🦱 Create Patient
        $patientUser = $userRepo->findOneBy(['email' => 'patient@hospital.com']);
        if (!$patientUser) {
            $patientUser = new User();
            $patientUser->setEmail('patient@hospital.com');
            $patientUser->setNom('Patient Demo');
            $patientUser->setRoles(['ROLE_PATIENT']);
            $patientPassword = $this->passwordHasher->hashPassword($patientUser, 'patient123');
            $patientUser->setPassword($patientPassword);
            $this->entityManager->persist($patientUser);
        }

        // Also create a Patient entity for the patient user (so rendez-vous can be linked)
        $existingPatientEntity = $patientRepo->findOneBy(['email' => 'patient@hospital.com']);
        if (!$existingPatientEntity) {
            $p = new Patient();
            $p->setPatientNumber('PAT' . date('YmdHis') . random_int(100, 999));
            $p->setFirstName('Patient');
            $p->setLastName('Demo');
            $p->setDateOfBirth(new \DateTime('2000-01-01'));
            $p->setAddress('N/A');
            $p->setCity('N/A');
            $p->setPostalCode('00000');
            $p->setEmail('patient@hospital.com');
            $this->entityManager->persist($p);
        }

        $this->entityManager->flush();

        $io->success('Doctor and Patient users created successfully!');
        $io->text('Doctor → doctor@hospital.com / doctor123');
        $io->text('Patient → patient@hospital.com / patient123');

        return Command::SUCCESS;
    }
}
