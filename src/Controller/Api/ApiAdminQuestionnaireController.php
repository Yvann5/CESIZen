<?php

namespace App\Controller\Api;

use App\Repository\QuestionnaireRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Questionnaire;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Repository\UtilisateurRepository;
use App\Entity\Utilisateur;



class ApiAdminQuestionnaireController extends AbstractController
{
    #[Route('/api/questionnaires/{id}', name: 'api_questionnaire_update', methods: ['PATCH'])]
    public function updateQuestionnaire(
        int $id,
        Request $request,
        QuestionnaireRepository $questionnaireRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $questionnaire = $questionnaireRepository->find($id);
        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['titre'])) {
            $questionnaire->setTitre($data['titre']);
        }
        if (isset($data['description'])) {
            $questionnaire->setDescription($data['description']);
        }

        $em->flush();

        return $this->json(['message' => 'Questionnaire modifié']);
    }

    #[Route('/api/questions/{id}', name: 'api_question_update', methods: ['PATCH'])]
    public function updateQuestion(
        int $id,
        Request $request,
        QuestionRepository $questionRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $question = $questionRepository->find($id);
        if (!$question) {
            return $this->json(['error' => 'Question non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['texte'])) {
            $question->setTexteQuestion($data['texte']);
        }

        $em->flush();

        return $this->json(['message' => 'Question modifiée']);
    }

    #[Route('/api/reponses/{id}', name: 'api_reponse_update', methods: ['PATCH'])]
    public function updateReponse(
        int $id,
        Request $request,
        ReponseRepository $reponseRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $reponse = $reponseRepository->find($id);
        if (!$reponse) {
            return $this->json(['error' => 'Réponse non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['texteReponse'])) {
            $reponse->setTexteReponse($data['texteReponse']);
        }
        if (isset($data['valeur'])) {
            $reponse->setValeur($data['valeur']);
        }

        $em->flush();

        return $this->json(['message' => 'Réponse modifiée']);
    }
    #[Route('/api/questionnaires', name: 'api_questionnaire_create', methods: ['POST'])]
    public function createQuestionnaire(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['titre'], $data['description'])) {
            return $this->json(['error' => 'Titre et description requis'], 400);
        }
        $questionnaire = new Questionnaire();
        $questionnaire->setTitre($data['titre']);
        $questionnaire->setDescription($data['description']);
        $em->persist($questionnaire);
        $em->flush();
        return $this->json(['id' => $questionnaire->getId()], 201);
    }

    #[Route('/api/questions', name: 'api_question_create', methods: ['POST'])]
    public function createQuestion(
        Request $request,
        QuestionnaireRepository $questionnaireRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['texte'], $data['questionnaireId'])) {
            return $this->json(['error' => 'Texte et questionnaireId requis'], 400);
        }
        $questionnaire = $questionnaireRepository->find($data['questionnaireId']);
        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire non trouvé'], 404);
        }
        $question = new Question();
        $question->setTexteQuestion($data['texte']);
        $question->setQuestionnaire($questionnaire);
        $em->persist($question);
        $em->flush();
        return $this->json(['id' => $question->getId()], 201);
    }

    #[Route('/api/reponses', name: 'api_reponse_create', methods: ['POST'])]
    public function createReponse(
        Request $request,
        QuestionRepository $questionRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['texteReponse'], $data['valeur'], $data['questionId'])) {
            return $this->json(['error' => 'texteReponse, valeur et questionId requis'], 400);
        }
        $question = $questionRepository->find($data['questionId']);
        if (!$question) {
            return $this->json(['error' => 'Question non trouvée'], 404);
        }
        $reponse = new Reponse();
        $reponse->setTexteReponse($data['texteReponse']);
        $reponse->setValeur($data['valeur']);
        $reponse->setQuestion($question);
        $em->persist($reponse);
        $em->flush();
        return $this->json(['id' => $reponse->getId()], 201);
    }

    #[Route('/api/questionnaires/{id}', name: 'api_questionnaire_delete', methods: ['DELETE'])]
    public function deleteQuestionnaire(
        int $id,
        QuestionnaireRepository $questionnaireRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $questionnaire = $questionnaireRepository->find($id);
        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire non trouvé'], 404);
        }
        $em->remove($questionnaire);
        $em->flush();
        return $this->json(null, 204);
    }

    #[Route('/api/utilisateurs', name: 'api_utilisateurs_list', methods: ['GET'])]
    public function listUsers(UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $users = $utilisateurRepository->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/utilisateurs/{id}/role', name: 'api_utilisateur_update_role', methods: ['PATCH'])]
public function updateRole(
    int $id,
    Request $request,
    UtilisateurRepository $userRepository,
    EntityManagerInterface $em,
    \App\Repository\RoleRepository $roleRepository // injecter le repository Role
): JsonResponse {
    $user = $userRepository->find($id);
    if (!$user) {
        return $this->json(['error' => 'Utilisateur non trouvé'], 404);
    }

    $data = json_decode($request->getContent(), true);

    if (!isset($data['role'])) { // j'utilise 'role' au singulier pour coller à ta structure
        return $this->json(['error' => 'Le champ role est requis'], 400);
    }

    $roleName = $data['role'];

    // Liste des rôles autorisés (noms des rôles en base)
    $allowedRoles = ['ROLE_USER', 'ROLE_ADMIN'];

    if (!in_array($roleName, $allowedRoles)) {
        return $this->json(['error' => 'Rôle non valide'], 400);
    }

    // Recherche le Role en base par son nom
    $role = $roleRepository->findOneBy(['nomRole' => $roleName]);
    if (!$role) {
        return $this->json(['error' => 'Rôle introuvable en base'], 404);
    }

    // Met à jour le rôle de l'utilisateur
    $user->setRole($role);
    $em->flush();

    return $this->json(['message' => 'Rôle mis à jour avec succès']);
}



    #[Route('/api/utilisateurs/{id}', name: 'api_utilisateur_delete', methods: ['DELETE'])]
    public function deleteUser(
        int $id,
        UtilisateurRepository $userRepository,  // <- ici c’est UtilisateurRepository
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(null, 204);
    }



}
