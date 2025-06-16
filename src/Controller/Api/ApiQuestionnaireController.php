<?php

namespace App\Controller\Api;

use App\Repository\QuestionnaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Entity\Question;
use App\Entity\Reponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use App\Entity\ResultatDiagnostic;
use App\Entity\Utilisateur;

class ApiQuestionnaireController extends AbstractController
{
    #[Route('/api/questionnaires', name: 'api_questionnaires', methods: ['GET'])]
    public function list(QuestionnaireRepository $questionnaireRepository): JsonResponse
    {
        $questionnaires = $questionnaireRepository->findAll();

        $data = [];

        foreach ($questionnaires as $q) {
            $questions = [];

            foreach ($q->getQuestions() as $question) {
                $questions[] = [
                    'id' => $question->getId(),
                    'texte' => $question->getTexteQuestion(),
                ];
            }

            $data[] = [
                'id' => $q->getId(),
                'titre' => $q->getTitre(),
                'description' => $q->getDescription(),
                'nbQuestions' => count($questions),
                'questions' => $questions,
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/questionnaires/{id}/questions', name: 'api_questionnaire_questions', methods: ['GET'])]
    public function questions(int $id, QuestionnaireRepository $questionnaireRepository): JsonResponse
    {
        $questionnaire = $questionnaireRepository->find($id);

        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire non trouvé'], 404);
        }

        $questions = $questionnaire->getQuestions();

        $data = [];

        foreach ($questions as $q) {
            $data[] = [
                'id' => $q->getId(),
                'texte' => $q->getTexteQuestion(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/questions/{id}/reponses', name: 'api_question_reponses', methods: ['GET'])]
    public function reponses(int $id, QuestionRepository $questionRepository): JsonResponse
    {
        $question = $questionRepository->find($id);

        if (!$question) {
            return $this->json(['error' => 'Question non trouvée'], 404);
        }

        $reponses = $question->getReponses();

        $data = [];

        foreach ($reponses as $reponse) {
            $data[] = [
                'id' => $reponse->getId(),
                'texteReponse' => $reponse->getTexteReponse(),
                'valeur' => $reponse->getValeur(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/questions/{id}', name: 'api_question_show', methods: ['GET'])]
    public function showQuestion(int $id, QuestionRepository $questionRepository): JsonResponse
    {
        $question = $questionRepository->find($id);

        if (!$question) {
            return $this->json(['error' => 'Question non trouvée'], 404);
        }

        return $this->json([
            'id' => $question->getId(),
            'texte' => $question->getTexteQuestion(),
        ]);
    }


    #[Route('/api/resultats-diagnostics', name: 'api_resultat_diagnostic_create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        QuestionnaireRepository $questionnaireRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['score'], $data['utilisateurId'], $data['questionnaireId'])) {
            return $this->json(['error' => 'Paramètres manquants : score, utilisateurId, questionnaireId'], 400);
        }

        $utilisateur = $utilisateurRepository->find($data['utilisateurId']);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $questionnaire = $questionnaireRepository->find($data['questionnaireId']);
        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire non trouvé'], 404);
        }

        $resultatDiagnostic = new ResultatDiagnostic();
        $resultatDiagnostic->setScore((int) $data['score']);
        $resultatDiagnostic->setUtilisateur($utilisateur);
        $resultatDiagnostic->setQuestionnaire($questionnaire);
        $resultatDiagnostic->setDate(new \DateTime());

        $em->persist($resultatDiagnostic);
        $em->flush();

        return $this->json([
            'message' => 'Résultat enregistré avec succès',
            'id' => $resultatDiagnostic->getId()
        ], 201);
    }

    #[Route('/api/questionnaires-utilisateur', name: 'api_questionnaires_utilisateur', methods: ['GET'])]
public function listWithStatus(Request $request, QuestionnaireRepository $questionnaireRepository, EntityManagerInterface $em): JsonResponse
{
    $utilisateurId = $request->query->get('utilisateurId');
    if (!$utilisateurId) {
        return $this->json(['error' => 'Paramètre utilisateurId manquant'], 400);
    }

    $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurId);
    if (!$utilisateur) {
        return $this->json(['error' => 'Utilisateur non trouvé'], 404);
    }

    $questionnaires = $questionnaireRepository->findAll();

    $resultats = $em->getRepository(ResultatDiagnostic::class)->findBy(['utilisateur' => $utilisateur]);

    $questionnairesFaits = [];
    foreach ($resultats as $res) {
        $questionnairesFaits[] = $res->getQuestionnaire()->getId();
    }

    $data = [];
    foreach ($questionnaires as $q) {
        $data[] = [
            'id' => $q->getId(),
            'titre' => $q->getTitre(),
            'description' => $q->getDescription(),
            'dejaFait' => in_array($q->getId(), $questionnairesFaits),
        ];
    }

    return $this->json($data);
}




}
