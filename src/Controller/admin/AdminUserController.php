<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Form\admin\UserFormType;
use App\Repository\UserRepository;
use App\Services\MailerService;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/admin/users', name: 'admin_users_')]
class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PaginatorInterface $paginator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserService $userService,
        private readonly MailerService $mailerService,
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        // gets users from db as query
        $users = $this->userRepository->getUsersAsQuery();
        // paginates users from query
        $paginator = $this->paginator->paginate($users, $request->query->getInt('page', 1), 20, ['distinct' => false]);

        return $this->render('admin/users/index.html.twig', [
            'users' => $paginator,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        //form init
        $form = $this->createForm(UserFormType::class);
        // handles request
        $form->handleRequest($request);
        //if form is submitted and is valid by values on the backend
        if($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $user = $form->getData();
                $user->setRoles(['ROLE_ADMIN']);
                //creates random password
                $randomPassword = $this->userService->generatePassword();
                //uses password hasher to hash password
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $randomPassword
                );
                //ssets hashed password
                $user->setPassword($hashedPassword);
                //saves user to database
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                //sends email to user mail
                $this->mailerService->sendAccountCreated($user->getEmail(), $randomPassword,  $this->generateUrl('admin_login', [], UrlGeneratorInterface::ABSOLUTE_URL));

                //returns success message
                $this->addFlash(
                    'success',
                    'Uživatel s emailem: '.$user->getEmail().' byl úspěšně přidán.'
                );

                //redirects to users overview
                return $this->redirectToRoute('admin_users_index');
            }
            catch (Exception $exception)
            {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: '.$exception->getMessage()
                );
            }
        }

        return $this->render('admin/users/create.html.twig', [
            'form' => $form->createView(),

        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(Request $request, User $user)
    {
        //form init
        $form = $this->createForm(UserFormType::class, $user);
        // handles request
        $form->handleRequest($request);
        //if form is submitted and is valid by values on the backend
        if($form->isSubmitted() && $form->isValid()) {
            try {
                //assigns form data to object
                $user = $form->getData();

                //saves changes to db
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                //returns success message
                $this->addFlash(
                    'success',
                    'Uživatel byl úspěšně upraven.'
                );

                //redirects to users overview
                return $this->redirectToRoute('admin_users_index');
            }
            catch (Exception $exception)
            {
                //in case of exception returns message
                $this->addFlash(
                    'error',
                    'Nastala neočekávaná vyjímka: '.$exception->getMessage()
                );
            }
        }

        return $this->render('admin/users/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(User $user)
    {
        try {
            // checks if user is not deleting himself
            if ($user->getId() == $this->getUser()->getId()) {
                $this->addFlash(
                    'error',
                    'Nelze smazat vlastní účet.'
                );
                return $this->redirectToRoute('admin_users_index');
            }

            // removes user from database
            $this->entityManager->remove($user);
            // saves changes
            $this->entityManager->flush();

            // returns success message
            $this->addFlash(
                'success',
                'Uživatel byl úspěšně smazán.'
            );
        }catch (Exception $exception){
            // in case of exception returns message
            $this->addFlash(
                'error',
                'Nastala neočekávaná vyjímka: '.$exception->getMessage()
            );
        }

        // redirects to users overview
        return $this->redirectToRoute('admin_users_index');
    }
}
