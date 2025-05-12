<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $texteReponse = null;

    #[ORM\Column(type: 'integer')]
    private ?int $valeur = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $utilisateur = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexteReponse(): ?string
    {
        return $this->texteReponse;
    }

    public function setTexteReponse(string $texteReponse): static
    {
        $this->texteReponse = $texteReponse;
        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(int $valeur): static
    {
        $this->valeur = $valeur;
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
}
