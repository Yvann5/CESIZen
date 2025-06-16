<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Entity\Questionnaire;
use Doctrine\ORM\EntityManagerInterface;

class DiagnosticControllerTest extends WebTestCase
{
    private function createUser($client, $email = 'diagtest@example.com')
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

        $user = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if ($user) {
            return $user;
        }

        $user = new Utilisateur();
        $user->setNom('Diag');
        $user->setPrenom('Test');
        $user->setEmail('testuser_' . uniqid() . '@example.com');
        $user->setPassword(password_hash('Password!123', PASSWORD_BCRYPT));
        $user->setRole($role);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createQuestionnaire($client)
    {
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $questionnaire = new Questionnaire();
        $questionnaire->setTitre('Test Questionnaire');
        $questionnaire->setDescription('Description test');
        $em->persist($questionnaire);
        $em->flush();

        return $questionnaire;
    }

    public function testDiagnosticPageAccessible()
    {
        $client = static::createClient();
        $client->request('GET', '/diagnostic');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testDiagnosticStartWithLogin()
    {
        $client = static::createClient();
        $user = $this->createUser($client);
        $questionnaire = $this->createQuestionnaire($client);

        $client->loginUser($user);
        $client->request('GET', '/diagnostic/' . $questionnaire->getId() . '/start');
        $this->assertTrue(
            $client->getResponse()->isRedirect() || $client->getResponse()->isRedirection()
        );
    }

    public function testDiagnosticResultPage()
    {
        $client = static::createClient();
        $client->request('GET', '/diagnostic/result?score=42');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '42');
    }
}