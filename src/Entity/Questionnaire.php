<?php

namespace App\Entity;

use App\Repository\QuestionnaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]
class Questionnaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'questionnaire', targetEntity: Question::class, cascade: ['persist', 'remove'])]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'questionnaire', targetEntity: UtilisateurQuestionnaire::class, cascade: ['persist', 'remove'])]
    private Collection $utilisateurQuestionnaires;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->utilisateurQuestionnaires = new ArrayCollection();
    }

    // Getters & Setters de base

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuestionnaire($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getQuestionnaire() === $this) {
                $question->setQuestionnaire(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UtilisateurQuestionnaire>
     */
    public function getUtilisateurQuestionnaires(): Collection
    {
        return $this->utilisateurQuestionnaires;
    }

    public function addUtilisateurQuestionnaire(UtilisateurQuestionnaire $utilisateurQuestionnaire): static
    {
        if (!$this->utilisateurQuestionnaires->contains($utilisateurQuestionnaire)) {
            $this->utilisateurQuestionnaires->add($utilisateurQuestionnaire);
            $utilisateurQuestionnaire->setQuestionnaire($this);
        }
        return $this;
    }

    public function removeUtilisateurQuestionnaire(UtilisateurQuestionnaire $utilisateurQuestionnaire): static
    {
        if ($this->utilisateurQuestionnaires->removeElement($utilisateurQuestionnaire)) {
            if ($utilisateurQuestionnaire->getQuestionnaire() === $this) {
                $utilisateurQuestionnaire->setQuestionnaire(null);
            }
        }
        return $this;
    }
}
