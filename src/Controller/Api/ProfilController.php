<?php
// src/Controller/ProfilController.php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

class ProfilController extends AbstractController
{
    private $entityManager;
    private $validator;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    #[Route('/api/profil', name: 'api_profil_get', methods: ['GET'])]
    public function getProfil(): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        // On renvoie les infos que l'on souhaite exposer (ex : nom, prenom, email)
        $data = [
            'nom' => $user->getNom(),        // adapte selon ta méthode getNom()
            'prenom' => $user->getPrenom(),  // adapte selon ta méthode getPrenom()
            'email' => $user->getEmail(),    // adapte selon ta méthode getEmail()
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/profil', name: 'api_profil_update', methods: ['PUT'])]
    public function updateProfil(Request $request): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Validation simple des champs requis
        if (!isset($data['nom'], $data['prenom'], $data['email'])) {
            return new JsonResponse(['error' => 'Champs manquants'], Response::HTTP_BAD_REQUEST);
        }

        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);

        // Tu peux ajouter une validation symfony ici si tu veux, exemple :
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Profil mis à jour avec succès']);
    }
}
