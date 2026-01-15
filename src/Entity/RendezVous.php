<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $reference = null;

    /**
     * Relation ManyToOne avec Patient
     * Chaque rendez-vous est pour un seul patient
     * Un patient peut avoir plusieurs rendez-vous
     */
    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    /**
     * Relation ManyToOne avec Medecin
     * Chaque rendez-vous est avec un seul médecin
     * Un médecin peut avoir plusieurs rendez-vous
     */
    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_heure = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duree = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $mode_paiement = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $paiement_effectue = false;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_annulation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $raison_annulation = null;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->statut = 'programme';
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): self
    {
        $this->medecin = $medecin;
        return $this;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->date_heure;
    }

    public function setDateHeure(\DateTimeInterface $date_heure): self
    {
        $this->date_heure = $date_heure;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): self
    {
        $this->motif = $motif;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(?string $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(?string $mode_paiement): self
    {
        $this->mode_paiement = $mode_paiement;
        return $this;
    }

    public function isPaiementEffectue(): ?bool
    {
        return $this->paiement_effectue;
    }

    public function setPaiementEffectue(?bool $paiement_effectue): self
    {
        $this->paiement_effectue = $paiement_effectue;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(?\DateTimeInterface $date_modification): self
    {
        $this->date_modification = $date_modification;
        return $this;
    }

    public function getDateAnnulation(): ?\DateTimeInterface
    {
        return $this->date_annulation;
    }

    public function setDateAnnulation(?\DateTimeInterface $date_annulation): self
    {
        $this->date_annulation = $date_annulation;
        return $this;
    }

    public function getRaisonAnnulation(): ?string
    {
        return $this->raison_annulation;
    }

    public function setRaisonAnnulation(?string $raison_annulation): self
    {
        $this->raison_annulation = $raison_annulation;
        return $this;
    }

    // Méthodes utilitaires
    public function annuler(string $raison = null): void
    {
        $this->statut = 'annule';
        $this->date_annulation = new \DateTime();
        $this->raison_annulation = $raison;
        $this->date_modification = new \DateTime();
    }

    public function confirmer(): void
    {
        $this->statut = 'confirme';
        $this->date_modification = new \DateTime();
    }

    public function terminer(): void
    {
        $this->statut = 'termine';
        $this->date_modification = new \DateTime();
    }

    public function getStatutLabel(): string
    {
        $statuts = [
            'programme' => 'Programmé',
            'confirme' => 'Confirmé',
            'annule' => 'Annulé',
            'termine' => 'Terminé',
            'absent' => 'Absent',
        ];
        
        return $statuts[$this->statut] ?? $this->statut;
    }

    public function getTypeLabel(): string
    {
        $types = [
            'consultation' => 'Consultation',
            'controle' => 'Contrôle',
            'urgence' => 'Urgence',
            'operation' => 'Opération',
            'examen' => 'Examen',
        ];
        
        return $types[$this->type] ?? $this->type;
    }
}