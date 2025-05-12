<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Entity\ResultatDiagnostic;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurQuestionnaire;
use App\Entity\Reponse;
use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\QuestionnaireRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;


final class DiagnosticController extends AbstractController{
    #[Route('/diagnostic', name: 'app_diagnostic')]
    public function index(QuestionnaireRepository $repo, Security $security, EntityManagerInterface $em): Response
    {
        $utilisateur = $security->getUser();

        $tousQuestionnaires = $repo->findAll();
        $questionnairesFaits = [];

        if ($utilisateur) {
            $resultats = $em->getRepository(ResultatDiagnostic::class)->findBy(['utilisateur' => $utilisateur]);
            foreach ($resultats as $res) {
                $questionnairesFaits[] = $res->getQuestionnaire()->getId();
            }
        }

        // Séparer ceux à faire et ceux déjà faits
        $questionnairesAFaire = array_filter($tousQuestionnaires, function ($q) use ($questionnairesFaits) {
            return !in_array($q->getId(), $questionnairesFaits);
        });

        $questionnairesFaitsObjets = array_filter($tousQuestionnaires, function ($q) use ($questionnairesFaits) {
            return in_array($q->getId(), $questionnairesFaits);
        });

        return $this->render('diagnostic/diagnostic.html.twig', [
            'questionnaires' => $questionnairesAFaire,
            'questionnairesFaits' => $questionnairesFaitsObjets,
        ]);
    }


    #[Route('/diagnostic/{id}/start', name: 'app_diagnostic_start')]
    public function start(Questionnaire $questionnaire, SessionInterface $session): Response
    {
        // Charger toutes les questions
        $questions = $questionnaire->getQuestions()->toArray();
        
        // Stocker dans la session l'avancement
        $session->set('current_questionnaire', $questionnaire->getId());
        $session->set('questions', array_map(fn($q) => $q->getId(), $questions));
        $session->set('current_index', 0);
        $session->set('score', 0); // Commencer à 0

        return $this->redirectToRoute('app_diagnostic_question');
    }

    #[Route('/diagnostic/question', name: 'app_diagnostic_question')]
    public function question(SessionInterface $session, QuestionRepository $repo, EntityManagerInterface $em, Security $security): Response
    {
        $questions = $session->get('questions', []);
        $currentIndex = $session->get('current_index', 0);

        if ($currentIndex >= count($questions)) {
            $score = $session->get('score', 0);

            $utilisateur = $security->getUser();
            if ($utilisateur) {
                $result = new ResultatDiagnostic();
                $result->setUtilisateur($utilisateur);
                $result->setScore($score);
                $result->setDate(new \DateTime());

                $questionnaireId = $session->get('current_questionnaire');
                $questionnaire = $em->getRepository(Questionnaire::class)->find($questionnaireId);
                $result->setQuestionnaire($questionnaire);

                $em->persist($result);
                $em->flush();
            }


            $session->remove('current_questionnaire');
            $session->remove('questions');
            $session->remove('current_index');
            $session->remove('score');

            return $this->redirectToRoute('app_diagnostic_result', ['score' => $score]);
        }

        // Sinon, continuer le questionnaire
        $questionId = $questions[$currentIndex];
        $question = $repo->find($questionId);

        return $this->render('diagnostic/question.html.twig', [
            'question' => $question,
            'reponses' => $question->getReponses(),
            'progress' => ($currentIndex + 1) . '/' . count($questions),
        ]);
    }



    #[Route('/diagnostic/answer', name: 'app_diagnostic_answer', methods: ['POST'])]
    public function answer(Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
        $reponseId = $request->request->get('reponse_id');

        if ($reponseId) {
            $reponse = $em->getRepository(\App\Entity\Reponse::class)->find($reponseId);

            if ($reponse) {
                $score = $session->get('score', 0);
                $session->set('score', $score + $reponse->getValeur());
            }
        }

        // Avancer l'index
        $currentIndex = $session->get('current_index', 0);
        $session->set('current_index', $currentIndex + 1);

        return $this->redirectToRoute('app_diagnostic_question');
    }


    #[Route('/diagnostic/result', name: 'app_diagnostic_result')]
    public function result(Request $request): Response
    {
        $score = $request->query->get('score');

        return $this->render('diagnostic/result.html.twig', [
            'score' => $score,
        ]);
    }




}
