<?php

namespace App\Controller;

use App\Repository\ContenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contenu;
use App\Form\ContenuType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        ContenuRepository $contenuRepository,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $contenus = $contenuRepository->findAll();

        // Vérifie s’il y a ?edit=ID dans l’URL
        $editId = $request->query->get('edit');
        $isEdit = false;

        if ($editId) {
            $contenu = $contenuRepository->find($editId);
            if (!$contenu) {
                throw $this->createNotFoundException('Contenu non trouvé');
            }
            $isEdit = true;
        } else {
            $contenu = new Contenu();
        }

        $form = $this->createForm(ContenuType::class, $contenu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('images_directory'), $newFilename);
                } catch (FileException $e) {
                    // Tu peux loguer ou afficher une erreur ici si tu veux
                }

                $contenu->setUrlImage($newFilename);
            }

            if (!$isEdit) {
                $contenu->setUtilisateur($this->getUser());
                $em->persist($contenu);
            }

            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'contenus' => $contenus,
            'form' => $form->createView(),
            'isEdit' => $isEdit,
            'contenuEditId' => $editId,
        ]);
    }
}
