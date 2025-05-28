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
}
