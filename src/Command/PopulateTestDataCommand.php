<?php

namespace App\Command;

use App\Entity\Hospital;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use App\Entity\Specialite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-test-data',
    description: 'Populate database with test data for medecins, patients, and appointments',
)]
class PopulateTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create Specialites
        $specialites = [];
        $specialiteNames = ['Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'Dermatology'];
        
        foreach ($specialiteNames as $name) {
            $specialite = new Specialite();
            $specialite->setNom($name);
            $specialite->setDescription('Department of ' . $name);
            $this->entityManager->persist($specialite);
            $specialites[] = $specialite;
        }
        $this->entityManager->flush();
        $io->success('Created ' . count($specialites) . ' specialites');

        // Create Hospital
        $hospital = new Hospital();
        $hospital->setNom('General Hospital');
        $hospital->setAdresse('123 Main Street');
        $hospital->setVille('New York');
        $this->entityManager->persist($hospital);
        $this->entityManager->flush();
        $io->success('Created hospital: ' . $hospital->getNom());

        // Create Medecins
        $medecins = [];
        $medecinData = [
            ['John', 'Smith', 'john.smith@hospital.com', '+1234567891'],
            ['Sarah', 'Johnson', 'sarah.j@hospital.com', '+1234567892'],
            ['Michael', 'Brown', 'michael.b@hospital.com', '+1234567893'],
            ['Emily', 'Davis', 'emily.d@hospital.com', '+1234567894'],
            ['David', 'Wilson', 'david.w@hospital.com', '+1234567895'],
        ];

        foreach ($medecinData as $index => $data) {
            $medecin = new Medecin();
            $medecin->setLicenseNumber('LIC' . str_pad($index + 1, 6, '0', STR_PAD_LEFT));
            $medecin->setFirstName($data[0]);
            $medecin->setLastName($data[1]);
            $medecin->setSpecialization($specialites[$index % count($specialites)]->getNom());
            $medecin->setEmail($data[2]);
            $medecin->setPhone($data[3]);
            $medecin->setDateOfBirth(new \DateTime('-' . (35 + $index) . ' years'));
            $medecin->setLicenseIssueDate(new \DateTime('-' . (5 + $index) . ' years'));
            $medecin->setLicenseExpiryDate(new \DateTime('+' . (5 - $index) . ' years'));
            $medecin->setAddress('Address ' . ($index + 1));
            $medecin->setCity('New York');
            $medecin->setPostalCode('1000' . ($index + 1));
            $medecin->setCountry('USA');
            $medecin->setIsActive(true);
            $medecin->setBiography('Experienced doctor with ' . (5 + $index) . ' years of practice');
            $medecin->setConsultationFee((100 + ($index * 50)) . '.00');
            $medecin->setNationality('American');
            $medecin->setGender($index % 2 === 0 ? 'Male' : 'Female');
            $medecin->setSpecialite($specialites[$index % count($specialites)]);
            $medecin->setHospital($hospital);
            
            $this->entityManager->persist($medecin);
            $medecins[] = $medecin;
        }
        $this->entityManager->flush();
        $io->success('Created ' . count($medecins) . ' medecins');

        // Create Patients
        $patients = [];
        $patientData = [
            ['Alice', 'Anderson', 'alice@email.com', '+1987654321'],
            ['Bob', 'Baker', 'bob@email.com', '+1987654322'],
            ['Charlie', 'Clark', 'charlie@email.com', '+1987654323'],
            ['Diana', 'Davis', 'diana@email.com', '+1987654324'],
            ['Edward', 'Evans', 'edward@email.com', '+1987654325'],
            ['Fiona', 'Foster', 'fiona@email.com', '+1987654326'],
            ['George', 'Green', 'george@email.com', '+1987654327'],
            ['Hannah', 'Harris', 'hannah@email.com', '+1987654328'],
        ];

        foreach ($patientData as $index => $data) {
            $patient = new Patient();
            $patient->setPatientNumber('PAT' . str_pad($index + 1, 6, '0', STR_PAD_LEFT));
            $patient->setFirstName($data[0]);
            $patient->setLastName($data[1]);
            $patient->setDateOfBirth(new \DateTime('-' . (25 + $index * 5) . ' years'));
            $patient->setGender($index % 2 === 0 ? 'Female' : 'Male');
            $patient->setEmail($data[2]);
            $patient->setPhone($data[3]);
            $patient->setAddress('Patient Address ' . ($index + 1));
            $patient->setCity('New York');
            $patient->setPostalCode('1000' . ($index + 1));
            $patient->setIsActive(true);
            
            $this->entityManager->persist($patient);
            $patients[] = $patient;
        }
        $this->entityManager->flush();
        $io->success('Created ' . count($patients) . ' patients');

        // Create RendezVous (Appointments)
        $rendezVousCount = 10;
        $types = ['Consultation', 'Follow-up', 'Emergency', 'Check-up'];
        $statuts = ['programme', 'confirme', 'termine', 'annule'];

        for ($i = 0; $i < $rendezVousCount; $i++) {
            $rdv = new RendezVous();
            $rdv->setReference('RDV' . str_pad($i + 1, 6, '0', STR_PAD_LEFT));
            $rdv->setPatient($patients[$i % count($patients)]);
            $rdv->setMedecin($medecins[$i % count($medecins)]);
            
            // Random date in the next 30 days
            $daysAhead = rand(1, 30);
            $rdv->setDateHeure(new \DateTime('+' . $daysAhead . ' days ' . rand(9, 17) . ':00'));
            
            $rdv->setType($types[$i % count($types)]);
            $rdv->setMotif('Medical consultation for treatment');
            $rdv->setStatut($statuts[$i % count($statuts)]);
            $rdv->setDuree(30);
            $rdv->setNotes('Patient requires regular monitoring');
            $rdv->setPrix((50 + ($i * 10)) . '.00');
            $rdv->setModePaiement('Cash');
            $rdv->setPaiementEffectue($i % 3 === 0);
            
            $this->entityManager->persist($rdv);
        }
        $this->entityManager->flush();
        $io->success('Created ' . $rendezVousCount . ' appointments');

        $io->success('All test data has been populated successfully!');
        $io->text('You can now view them in the admin dashboard');

        return Command::SUCCESS;
    }
}
