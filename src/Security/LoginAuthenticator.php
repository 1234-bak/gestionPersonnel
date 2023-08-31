<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $matricule = $request->request->get('matricule', '');

        $request->getSession()->set(Security::LAST_USERNAME, $matricule);

        return new Passport(
            new UserBadge($matricule),
            new PasswordCredentials($request->request->get('password', '')),
            [
                (new RememberMeBadge())->enable(),
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin'));
        }
        elseif (in_array('ROLE_GRH', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('grh'));
        }
        elseif (in_array('ROLE_CS', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('cs'));
        } 
        elseif (in_array('ROLE_DIR', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('dir'));
        } 
        elseif (in_array('ROLE_SD', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('sd'));
        }
        elseif (in_array('ROLE_DIRCAB', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('dircab'));
        } 

        elseif (in_array('ROLE_USER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('user'));
        } 
        
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
