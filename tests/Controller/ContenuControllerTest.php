<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;

class ContenuControllerTest extends WebTestCase
{
    private function createUserWithRole($client, $email = 'testuser@example.com')
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
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setEmail('testuser_' . uniqid() . '@example.com');
        $user->setPassword(password_hash('Password!123', PASSWORD_BCRYPT));
        $user->setRole($role);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testCreateContenuMissingFields()
    {
        $client = static::createClient();
        $client->request('POST', '/api/contenus', [], [], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreateContenuWithValidData()
    {
        $client = static::createClient();
        $user = $this->createUserWithRole($client);

        $imagePath = sys_get_temp_dir() . '/test_image.jpg';
        file_put_contents($imagePath, 'fake image content');
        $image = new UploadedFile($imagePath, 'test.jpg', 'image/jpeg', null, true);

        $client->request(
            'POST',
            '/api/contenus',
            [
                'titre' => 'Titre test',
                'texte' => 'Texte test',
                'user_id' => $user->getId(),
            ],
            [
                'image' => $image,
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    public function testListContenus()
    {
        $client = static::createClient();
        $client->request('GET', '/api/contenus');
        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testUpdateContenuNotFound()
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/contenus/99999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['titre' => 'Nouveau titre']));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteContenuNotFound()
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/contenus/99999');
        $this->assertResponseStatusCodeSame(404);
    }
}