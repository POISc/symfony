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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function createNewUser(Request $request, DepartmentRepository $departmentRepository, SluggerInterface $slugger, ValidatorInterface $validator): Response
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

        $file = $request->files->get('avatar');
        if ($file) 
        {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid();
        
            $projectDir = $this->getParameter('kernel.project_dir');
            try {
                $file->move($projectDir . "/public/uploads/avatars", $newFilename);
            } catch (FileException $e) {
                throw new FileException('Не удалось загрузить файл: '.$e->getMessage());
            }
    
            $user->setAvatar("/uploads/avatars/" . $newFilename); 
        }

        $errors = $validator->validate($user);
        if(count($errors) > 0)
        {
            return $this->render('user/create.html.twig', ['departments' => $departmentRepository->findAll(), 'errors' => $errors]);
        }
        
        $this->entityManager->persist($user);
        $this->entityManager->flush(); 

        return $this->redirect('http://localhost:8000/user');
    }

    #[Route(path:"/user/{id}", name: 'change_user_form', methods: ['GET'])]
    public function updateForm( int $id, DepartmentRepository $departmentRepository): Response
    {
        return $this->render('user/change.html.twig', ['user' => $this->userRepository->find($id), 'departments' => $departmentRepository->findAll()]);
    }

    #[Route(path:"/user/{id}", name: 'changeing_user', methods: ['PUT'])]
    public function update(int $id, Request $request, DepartmentRepository $departmentRepository, SluggerInterface $slugger, ValidatorInterface $validator): Response
    {
        $user = $this->userRepository->find($id);

        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setAge($request->request->get('age'));
        $user->setStatus($request->request->get('status'));
        $user->setEmail($request->request->get('email'));
        $user->setTelegram($request->request->get('telegram'));
        $user->setAddres($request->request->get('addres'));
        $user->setDepartment($departmentRepository->find($request->request->get('department')));

        $file = $request->files->get('avatar');
        if ($file) 
        {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid();
        
            $projectDir = $this->getParameter('kernel.project_dir');
            $uploadDir = $projectDir . '/public/uploads/avatars';
            try {
                $file->move($projectDir . "/public/uploads/avatars", $newFilename);
            } catch (FileException $e) {
                throw new FileException('Не удалось загрузить файл: '.$e->getMessage());
            }

            if($user->getAvatar() != null && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $user->getAvatar()))
            {
                unlink($this->getParameter('kernel.project_dir') . '/public/' . $user->getAvatar());
            }
    
            $user->setAvatar("/uploads/avatars/" . $newFilename); 
        }

        $errors = $validator->validate($user);
        if(count($errors) > 0)
        {
            return $this->render('user/change.html.twig', ['user' => $this->userRepository->find($id), 'departments' => $departmentRepository->findAll(), 'errors' => $errors]);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/user');
    }

    #[Route(path:"/user/{id}/delete", name: 'delete_user', methods: ['DELETE'])]
    public function delete(int $id, Request $request): Response
    {
        $user = $this->userRepository->find($id);
        if($user->getAvatar() != null && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $user->getAvatar()))
        {
            unlink($this->getParameter('kernel.project_dir') . '/public/' . $user->getAvatar());
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/user');
    }
}
