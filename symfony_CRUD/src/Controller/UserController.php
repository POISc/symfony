<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DepartmentRepository;
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

        $qb = $this->userRepository->createQueryBuilder('request');

        if(isset($data) && $data != '')
        {
            $qb->leftJoin('request.department', 'department')
            ->where('request.first_name LIKE :search')
            ->orWhere('request.last_name LIKE :search')
            ->orWhere('request.email LIKE :search')
            ->orWhere('request.telegram LIKE :search')
            ->orWhere('department.name LIKE :search')
            ->setParameter('search', '%' . $data . '%');
        }

        $sortable = $request->query->get('sortable');
        $sortType = $request->query->get('sortType');

        $qb->orderBy(isset($sortable)?$sortable:'request.first_name', isset($sortType)?$sortType:'ASC');

        return $this->render('user/index.html.twig', ['users' => $qb->getQuery()->getResult()]);
    }

    #[Route(path:"/user/create", name: 'create_user', methods: ['GET'])]
    public function formCreate(DepartmentRepository $departmentRepository): Response
    {
        
        return $this->render('user/create.html.twig', ['departments' => $departmentRepository->findAll()]);
    }

    #[Route(path:"/user/create", name: 'creating_user', methods: ['POST'])]
    public function createNewUser(Request $request, DepartmentRepository $departmentRepository): Response
    {

        $user = new User();
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setAge($request->request->get('age'));
        $user->setStatus($request->request->get('status'));
        $user->setEmail($request->request->get('email'));
        $user->setTelegram($request->request->get('telegram'));
        $user->setAddres($request->request->get('addres'));
        $user->setDepartment($departmentRepository->find($request->request->get('department')));
        
        $this->entityManager->persist($user);
        $this->entityManager->flush(); 

        return $this->redirect('http://localhost:8000/user');
    }

    #[Route(path:"/user/{id}", name: 'change_user_form', methods: ['GET'])]
    public function updateForm( int $id): Response
    {
        return $this->render('user/change.html.twig', ['user' => $this->userRepository->find($id)]);
    }

    #[Route(path:"/user/{id}", name: 'changeing_user', methods: ['PUT'])]
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
        $user->setDepartment($request->request->get('departments'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/user');
    }

    #[Route(path:"/user/{id}/delete", name: 'delete_user', methods: ['DELETE'])]
    public function delete(int $id, Request $request): Response
    {
        $this->entityManager->remove($this->userRepository->find($id));
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/user');
    }
}
