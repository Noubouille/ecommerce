<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;
use App\Entity\Order;

class UserController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
    * @Route("/api/register", name="user_register", methods={"POST"})
    */
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setLogin($data['login']);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setPassword($hashedPassword);
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstname']);
        $user->setLastName($data['lastname']);
        $em->persist($user);
        $em->flush();

        $data = [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'password' => $user->getPassword(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
        ];

        return $this->json(['message' => "A new user has been created"], Response::HTTP_OK);
    }

    /**
    * @Route("/api/users", name="users", methods={"GET"})
    */

    public function index(): Response
    {
        $users = $this->doctrine
            ->getRepository(User::class)
            ->findAll();

        if (!$users) {
            return $this->json(['error' => "Can't parse users."], Response::HTTP_NOT_FOUND);
        }
 
        $data = [];
 
        foreach ($users as $user) {
           $data[] = [
               'id' => $user->getId(),
               'login' => $user->getLogin(),
               'password' => $user->getPassword(),
               'email' => $user->getEmail(),
               'firstname' => $user->getFirstName(),
               'lastname' => $user->getLastName(),
           ];
        }
        return $this->json($data, Response::HTTP_OK);
    }

    /**
    * @Route("/api/user/{id}", name="lol", methods={"GET"})
    */
    public function getOrders(int $id) : Response {
        $users = $this->doctrine->getRepository(User::class)->find($id);

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'orders' => $user->getOrders(),
            ];
         }

         return $this->json($data, Response::HTTP_OK);
    }
}