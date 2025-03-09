<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/user', name: 'app_user')]
    public function index(Request $request): Response
    {
        $data = $request->query->get('soughtAfter');

        if(isset($data) && $data != '')
        {
            $qb = $this->userRepository->createQueryBuilder('request');
            $qb->where($qb->expr()->like('request.first_name', ':search'))
            ->orWhere($qb->expr()->like('request.last_name', ':search'))
            ->orWhere($qb->expr()->like('request.email', ':search'))
            ->orWhere($qb->expr()->like('request.telegram', ':search'))
            ->setParameter('search', '%' . $data . '%');

            return $this->render('user/index.html.twig', ['users' => $qb->getQuery()->getResult()]);
        } else
        {
            return $this->render('user/index.html.twig', [
                'users' => $this->userRepository->findAll(),
            ]);
        }
    }

    #[Route(path:"/user/create", name: 'create_user', methods: ['GET'])]
    public function formCreate(): Response
    {
        
        return $this->render('user/create.html.twig');
    }

    #[Route(path:"/user/create", name: 'creating_user', methods: ['POST'])]
    public function createNewUser(Request $request): Response
    {

        $user = new User();
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setAge($request->request->get('age'));
        $user->setStatus($request->request->get('status'));
        $user->setEmail($request->request->get('email'));
        $user->setTelegram($request->request->get('telegram'));
        $user->setAddres($request->request->get('addres'));
        
        $this->entityManager->persist($user);
        $this->entityManager->flush(); 

        return $this->redirect('http://localhost:8000/user');
    }

    #[Route(path:"/user/{id}", name: 'change_user_form', methods: ['GET'])]
    public function updateForm( int $id): Response
    {
        return $this->render('user/change.html.twig', ['user' => $this->userRepository->find($id)]);
    }

    #[Route(path:"/user/{id}/delete", name: 'changeing_user', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $user = $this->userRepository->find($id);

        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setAge($request->request->get('age'));
        $user->setStatus($request->request->get('status'));
        $user->setEmail($request->request->get('email'));
        $user->setTelegram($request->request->get('telegram'));
        $user->setAddres($request->request->get('addres'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/user');
    }

    #[Route(path:"/user/{id}", name: 'delete_user', methods: ['DELETE'])]
    public function delete(int $id, Request $request): Response
    {
        $this->entityManager->remove($this->userRepository->find($id));
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/user');
    }
}
