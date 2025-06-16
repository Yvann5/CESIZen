<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;

class ProfilControllerTest extends WebTestCase
{
    private function createUser($client, $email = 'profiltest@example.com')
    {
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $role = $em->getRepository(Role::class)->findOneBy(['nomRole' => 'ROLE_USER']);
        if (!$role) {
            $role = new Role();
            $role->setNomRole('ROLE_USER');
            $em->persist($role);
            $em->flush();
        }

        $user = new Utilisateur();
        $user->setNom('Profil');
        $user->setPrenom('Test');
        $user->setEmail('testuser_' . uniqid() . '@example.com');
        $user->setPassword(password_hash('Password!123', PASSWORD_BCRYPT));
        $user->setRole($role);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testGetProfilUnauthenticated()
    {
        $client = static::createClient();
        $client->request('GET', '/api/profil');
        $this->assertResponseStatusCodeSame(401);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetProfilAuthenticated()
    {
        $client = static::createClient();
        $user = $this->createUser($client);

        // Simule l'authentification
        $client->loginUser($user);

        $client->request('GET', '/api/profil');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Profil', $data['nom']);
        $this->assertEquals('Test', $data['prenom']);
        $this->assertEquals($user->getEmail(), $data['email']);
    }

    public function testUpdateProfil()
    {
        $client = static::createClient();
        $email = 'profilupdate_' . uniqid() . '@example.com';
        $user = $this->createUser($client, $email);
        $client->loginUser($user);

        $newEmail = 'profilupdate_new_' . uniqid() . '@example.com';

        $payload = [
            'nom' => 'NouveauNom',
            'prenom' => 'NouveauPrenom',
            'email' => $newEmail
        ];

        $client->request(
            'PUT',
            '/api/profil',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Profil mis à jour avec succès', $data['message']);
    }
}