<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OTPHP\TOTP;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

class TwoFactorController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/code-login', name: 'app_code_login')]
    public function codeLogin(Request $request): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('User not logged in.');
        }

        $secret = $user->getGoogleAuthenticatorSecret();

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            // Vérifier si le code entré est correct en utilisant OTPHP
            $totp = TOTP::create($secret);

            if ($totp->verify($code)) {
                // Si le code est valide, rediriger vers la page d'accueil
                return $this->redirectToRoute('home');
            } else {
                // Si le code est invalide, afficher un message d'erreur
                $this->addFlash('error', 'Invalid code.');
            }
        }

        // Afficher le formulaire de saisie du code 2FA
        return $this->render('security/codelogin.html.twig');
    }
}