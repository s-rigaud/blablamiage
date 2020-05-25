<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SecurityController extends AbstractController
{

    private $security;

    public function __construct(Security $security){
        $this->security = $security;
    }

    /**
     * Method which allowed a user to login
    * @Route("/login", name="login")
    */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //check if the user is defined
        if ($this->getUser()){
            //navigate to the home page
            return $this->redirectToRoute('account', [], 301);
        }
        //informs the user that his login fail
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', [
            'breadcrum' => 'Home > Log in',
            'error' => $error,
        ]);
    }

    /**
     * Method which allowed a user to login
    * @Route("/register", name="register")
    */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $om): Response
    {
        //check if the user is already logged
        if ($this->getUser()){
            return $this->redirect($request->headers->get('referer'));
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $om->persist($user);
            $om->flush();

            $this->addFlash('success', "Your account have been created, you can login now");
            return $this->redirect('login');
        }
        return $this->render('security/register.html.twig', [
            'breadcrum' => 'Home > Register',
            'form' => $form->createView(),
        ]);
    }
}
