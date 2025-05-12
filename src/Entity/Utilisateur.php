<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 60, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez entrer un email valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*[\W_]).+$/',
        message: 'Le mot de passe doit contenir au moins une majuscule et un caractère spécial.'
    )]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Reponse::class, cascade: ['persist', 'remove'])]
    private Collection $reponses;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: ResultatDiagnostic::class, cascade: ['persist', 'remove'])]
    private Collection $resultatsDiagnostics;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: UtilisateurQuestionnaire::class, cascade: ['persist', 'remove'])]
    private Collection $utilisateurQuestionnaires;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->resultatsDiagnostics = new ArrayCollection();
        $this->utilisateurQuestionnaires = new ArrayCollection();
    }

    // ------------------- GETTERS / SETTERS -------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    // ------------------- RELATIONS -------------------

    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setUtilisateur($this);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getUtilisateur() === $this) {
                $reponse->setUtilisateur(null);
            }
        }
        return $this;
    }

    public function getResultatsDiagnostics(): Collection
    {
        return $this->resultatsDiagnostics;
    }

    public function addResultatDiagnostic(ResultatDiagnostic $resultatDiagnostic): static
    {
        if (!$this->resultatsDiagnostics->contains($resultatDiagnostic)) {
            $this->resultatsDiagnostics->add($resultatDiagnostic);
            $resultatDiagnostic->setUtilisateur($this);
        }
        return $this;
    }

    public function removeResultatDiagnostic(ResultatDiagnostic $resultatDiagnostic): static
    {
        if ($this->resultatsDiagnostics->removeElement($resultatDiagnostic)) {
            if ($resultatDiagnostic->getUtilisateur() === $this) {
                $resultatDiagnostic->setUtilisateur(null);
            }
        }
        return $this;
    }

    public function getUtilisateurQuestionnaires(): Collection
    {
        return $this->utilisateurQuestionnaires;
    }

    public function addUtilisateurQuestionnaire(UtilisateurQuestionnaire $utilisateurQuestionnaire): static
    {
        if (!$this->utilisateurQuestionnaires->contains($utilisateurQuestionnaire)) {
            $this->utilisateurQuestionnaires->add($utilisateurQuestionnaire);
            $utilisateurQuestionnaire->setUtilisateur($this);
        }
        return $this;
    }

    public function removeUtilisateurQuestionnaire(UtilisateurQuestionnaire $utilisateurQuestionnaire): static
    {
        if ($this->utilisateurQuestionnaires->removeElement($utilisateurQuestionnaire)) {
            if ($utilisateurQuestionnaire->getUtilisateur() === $this) {
                $utilisateurQuestionnaire->setUtilisateur(null);
            }
        }
        return $this;
    }

    // ------------------- SECURITE SYMFONY -------------------

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];
        if ($this->role) {
            $roles[] = $this->role->getNomRole();
        }
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }


    public function getSalt(): ?string
    {
        // Pas nécessaire avec bcrypt/argon2
        return null;
    }

    public function eraseCredentials(): void
    {
        // Si tu avais un champ temporaire genre plainPassword, tu l'effacerais ici
    }
}
