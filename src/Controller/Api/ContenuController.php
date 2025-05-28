<?php

namespace App\Controller\Api;

use App\Repository\ContenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\Contenu;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;


#[Route('/api/contenus', name: 'api_contenus_')]
class ContenuController extends AbstractController
{

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $em): JsonResponse
    {
        $titre = $request->request->get('titre');
        $texte = $request->request->get('texte');
        $imageFile = $request->files->get('image');
        $userId = $request->request->get('user_id');

        if (!$titre || !$texte || !$imageFile || !$userId) {
            return $this->json(['error' => 'Champs manquants'], Response::HTTP_BAD_REQUEST);
        }

        $user = $utilisateurRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_BAD_REQUEST);
        }

        // Upload image
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
        $newFilename = uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move($uploadsDir, $newFilename);
        } catch (FileException $e) {
            return $this->json(['error' => 'Erreur upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Création du contenu
        $contenu = new Contenu();
        $contenu->setTitre($titre);
        $contenu->setTexte($texte);
        $contenu->setUrlImage($newFilename);
        $contenu->setUtilisateur($user);

        $em->persist($contenu);
        $em->flush();

        return $this->json(['message' => 'Contenu créé avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        Request $request,
        ContenuRepository $contenuRepository,
        EntityManagerInterface $em,
        UtilisateurRepository $userRepository, // ajoute-le ici
        int $id
    ): JsonResponse {
        $contenu = $contenuRepository->find($id);

        if (!$contenu) {
            return new JsonResponse(['error' => 'Contenu non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['titre'])) {
            $contenu->setTitre($data['titre']);
        }

        if (isset($data['texte'])) {
            $contenu->setTexte($data['texte']);
        }

        if (isset($data['user_id'])) {
            $user = $userRepository->find($data['user_id']);
            $contenu->setUtilisateur($user);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Contenu mis à jour']);
    }





    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, ContenuRepository $contenuRepository, EntityManagerInterface $em): JsonResponse
    {
        $contenu = $contenuRepository->find($id);
        if (!$contenu) {
            return $this->json(['error' => 'Contenu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($contenu);
        $em->flush();

        return $this->json(['message' => 'Contenu supprimé avec succès']);
    }






    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ContenuRepository $contenuRepository): JsonResponse
    {
        // Change cette URL selon ton environnement (localhost, prod, etc.)
        $baseUrl = 'http://10.0.2.2:8000';

        $contenus = $contenuRepository->findAll();

        $data = [];

        foreach ($contenus as $contenu) {
            $data[] = [
                'id' => $contenu->getId(),
                'titre' => $contenu->getTitre(),
                'texte' => $contenu->getTexte(),
                'urlImage' => $baseUrl . '/uploads/images/' . $contenu->getUrlImage(),
                'utilisateur' => [
                    'prenom' => $contenu->getUtilisateur()->getPrenom(),
                    'nom' => $contenu->getUtilisateur()->getNom(),
                ],
            ];
        }

        return $this->json($data);
    }
}
