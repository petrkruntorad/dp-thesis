<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Form\admin\AdminChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/profile', name: 'admin_profile_')]
class AdminProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    #[Route('/password-change', name: 'password_change')]
    public function password_change(Request $request)
    {
        $currentUser = $this->getUser();
        if($currentUser instanceof User){
            //inits form
            $form = $this->createForm(AdminChangePasswordFormType::class);
            $form->handleRequest($request);

            //if form is submitted and is valid by settings on the backend
            if($form->isSubmitted() && $form->isValid()) {
                try {
                    //assigns value from form
                    $plainPassword = $form->get('plainPassword')->getData();

                    //hashes password
                    $hashedPassword = $this->passwordHasher->hashPassword(
                        $currentUser,
                        $plainPassword
                    );

                    //sets hashed password to database
                    $currentUser->setPassword($hashedPassword);

                    //saves changes
                    $this->em->persist($currentUser);
                    $this->em->flush();

                    //returns error message
                    $this->addFlash(
                        'success',
                        'Heslo bylo úšpěšně změněno'
                    );
                }
                catch (Exception $exception)
                {
                    //in case of exception returns message
                    $this->addFlash(
                        'error',
                        'Nastala neočekávaná vyjímka: '.$exception
                    );
                }
            }
        }else{
            $this->addFlash(
                'error',
                'Neopravněné využití aplikace.'
            );
        }


        return $this->render('admin/profile/password-change.html.twig', [
            'form' => $form->createView(),

        ]);
    }
}
