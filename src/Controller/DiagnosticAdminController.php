<?php

// src/Controller/DiagnosticAdminController.php
namespace App\Controller;

use App\Entity\Questionnaire;
use App\Form\QuestionnaireType;
use App\Repository\QuestionnaireRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\RoleRepository;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class DiagnosticAdminController extends AbstractController
{
    #[Route('/admin/diagnostic', name: 'app_diagnostic_admin')]
    public function index(EntityManagerInterface $em, UtilisateurRepository $utilisateurRepo, RoleRepository $roleRepo): Response
    {
        // Utiliser l'EntityManagerInterface pour récupérer les questionnaires
        $questionnaires = $em->getRepository(Questionnaire::class)->findAll();
        $utilisateurs = $utilisateurRepo->findAll();
        $roles = $roleRepo->findAll();

        return $this->render('diagnostic_admin/pageadmin.html.twig', [
            'questionnaires' => $questionnaires,
            'utilisateurs' => $utilisateurs,
            'roles' => $roles,
        ]);
    }

    #[Route('/admin/diagnostic/create', name: 'app_diagnostic_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $questionnaire = new Questionnaire();
        $form = $this->createForm(QuestionnaireType::class, $questionnaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $questionnaire = $form->getData();

            foreach ($questionnaire->getQuestions() as $question) {
                foreach ($question->getReponses() as $reponse) {
                    // forcer la relation inverse (sécurité)
                    $reponse->setQuestion($question);
                }
            }
            // Persister le questionnaire
            $em->persist($questionnaire);
            $em->flush();

            // Ajouter un message flash
            $this->addFlash('success', 'Le questionnaire a été créé avec succès.');

            return $this->redirectToRoute('app_diagnostic_admin');
        }

        return $this->render('diagnostic_admin/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/diagnostic/{id}/edit', name: 'app_diagnostic_edit')]
    public function edit(Questionnaire $questionnaire, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuestionnaireType::class, $questionnaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le questionnaire a été modifié avec succès.');

            return $this->redirectToRoute('app_diagnostic_admin');
        }

        return $this->render('diagnostic_admin/edit.html.twig', [
            'form' => $form->createView(),
            'questionnaire' => $questionnaire,
        ]);
    }

    #[Route('/admin/diagnostic/{id}/delete', name: 'app_diagnostic_delete')]
    public function delete(Questionnaire $questionnaire, EntityManagerInterface $em): Response
    {
        $em->remove($questionnaire);
        $em->flush();

        $this->addFlash('success', 'Le questionnaire a été supprimé avec succès.');

        return $this->redirectToRoute('app_diagnostic_admin');
    }

    #[Route('/admin/user/{id}/changer-role', name: 'admin_change_user_role', methods: ['POST'])]
    public function changeUserRole(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $roleId = $request->request->get('role_id');
        $role = $em->getRepository(Role::class)->find($roleId);

        if ($role) {
            $utilisateur->setRole($role);
            $em->flush();

            $this->addFlash('success', 'Rôle mis à jour avec succès.');
        }

        return $this->redirectToRoute('app_diagnostic_admin');
    }

    #[Route('/admin/utilisateur/{id}/supprimer', name: 'supprimer_utilisateur', methods: ['POST'])]
    public function supprimerUtilisateur(Utilisateur $utilisateur, EntityManagerInterface $em): Response
    {
        $em->remove($utilisateur);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('app_diagnostic_admin');
    }

}

