<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProfilType;
use App\Form\MotDePasseType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Entity\Questionnaire;
use App\Repository\UtilisateurRepository;
use App\Repository\RoleRepository;
use App\Repository\QuestionnaireRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProfilController extends AbstractController{
    #[Route('/profil', name: 'app_profil')]
    public function profil(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $utilisateur = $this->getUser();

        // Formulaire infos
        $formInfos = $this->createForm(ProfilType::class, $utilisateur);
        $formInfos->handleRequest($request);

        if ($formInfos->isSubmitted() && $formInfos->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Informations mises à jour.');
            return $this->redirectToRoute('app_profil');
        }

        // Formulaire mot de passe
        $formPassword = $this->createForm(MotDePasseType::class);
        $formPassword->handleRequest($request);

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $newPassword = $formPassword->get('plainPassword')->getData();
            $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $newPassword));
            $em->flush();
            $this->addFlash('success', 'Mot de passe modifié.');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/profil.html.twig', [
            'form_infos' => $formInfos->createView(),
            'form_password' => $formPassword->createView(),
        ]);
    }

}
