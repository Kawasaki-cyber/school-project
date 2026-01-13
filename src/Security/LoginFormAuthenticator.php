<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        // Get email from _username field (Symfony form convention)
        $email = $request->request->get('_username', '');
        
        // Fallback: try 'email' field if _username is not set
        if (empty($email)) {
            $email = $request->request->get('email', '');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Get password from _password field (Symfony form convention)
        $password = $request->request->get('_password', '');
        
        // Fallback: try 'password' field if _password is not set
        if (empty($password)) {
            $password = $request->request->get('password', '');
        }

        // Get CSRF token
        $csrfToken = $request->request->get('_csrf_token', '');

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        // Get the user from the token
        $user = $token->getUser();
        
        // Check if user has ROLE_ADMIN
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        }
        
        // Check if user has ROLE_MEDECIN
        if ($user && in_array('ROLE_MEDECIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        }
        
        // Check if user has ROLE_PATIENT
        if ($user && in_array('ROLE_PATIENT', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_rendezvous'));
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Default redirect to admin dashboard or home
        return new RedirectResponse($this->urlGenerator->generate('app_admin'));
    }


    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
