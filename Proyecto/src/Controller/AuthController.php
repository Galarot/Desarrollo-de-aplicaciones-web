<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $username = trim($request->request->get('username', ''));
            $password = $request->request->get('password', '');
            $confirm = $request->request->get('confirm_password', '');

            if (!$email || !$username || !$password || !$confirm) {
                $error = 'Todos los campos son obligatorios.';
            } elseif ($password !== $confirm) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (strlen($password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $error = 'Ya existe una cuenta con ese correo.';
            } elseif ($em->getRepository(User::class)->findOneBy(['username' => $username])) {
                $error = 'Ese nombre de usuario ya está en uso.';
            } else {
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setPassword($hasher->hashPassword($user, $password));
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/register.html.twig', ['error' => $error]);
    }
}
