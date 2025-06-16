<?php 
namespace App\Controller\Api;

use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['prenom'], $data['nom'], $data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Champs manquants'], 400);
        }

        $existingUser = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], 409);
        }

        $user = new Utilisateur();
        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setEmail($data['email']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $role = $em->getRepository(Role::class)->findOneBy(['nomRole' => 'ROLE_USER']);
        if (!$role) {
            return new JsonResponse(['error' => 'Rôle utilisateur introuvable'], 500);
        }
        $user->setRole($role);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Compte créé avec succès'], 201);
    }
}
