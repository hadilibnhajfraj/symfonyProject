<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;

class ApiContactController extends AbstractController
{
    #[Route('/api/contact', name: 'app_api_contact')]
    public function index(): Response
    {
        return $this->render('api_contact/index.html.twig', [
            'controller_name' => 'ApiContactController',
        ]);
    }
    
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/add', name: 'add')]
   
    public function addStudent(Request $request): JsonResponse
    {
        $client = new Client();
        $form = $this->createForm(ContactFormType::class, $client);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;

            // Vérifiez si l'email existe déjà dans la base de données
            $existingClient = $em->getRepository(Client::class)->findOneBy(['email' => $client->getEmail()]);

            if ($existingClient) {
                // L'email existe déjà, renvoyez le statut 409 (Conflit) en tant que réponse JSON
                return new JsonResponse(['message' => 'L\'email existe déjà.'], 409);
            } else {
                // L'email n'existe pas encore, vous pouvez ajouter le client
                $em->persist($client);
                $em->flush();
                return new JsonResponse(['message' => 'Client ajouté avec succès.'], 200);
            }
        }

        return new JsonResponse(['message' => 'Entrez un email valide.'], 400);
    }
   
   
    
}
