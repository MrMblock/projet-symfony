<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;

class GoogleAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // 1) Cherche l'utilisateur par son googleId
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2) Cherche l'utilisateur par son email (si profil créé avant le bouton Google)
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $existingUser->setGoogleId($googleUser->getId());
                    if (!$existingUser->getGoogleAvatar()) {
                        $existingUser->setGoogleAvatar($googleUser->getAvatar());
                    }
                    $this->entityManager->flush();

                    return $existingUser;
                }

                // 3) Sinon, crée un nouvel utilisateur (Inscription automatique)
                $user = new User();
                $user->setEmail($email);
                $user->setGoogleId($googleUser->getId());
                $user->setFirstName($googleUser->getFirstName());
                $user->setLastName($googleUser->getLastName());
                $user->setGoogleAvatar($googleUser->getAvatar());
                
                // Génération d'un slug
                $slugger = new AsciiSlugger();
                $slug = strtolower($slugger->slug($user->getFirstName() . '-' . $user->getLastName())->toString());
                $user->setSlug($slug);

                // Mot de passe aléatoire (non utilisé pour login Google)
                $user->setPassword(bin2hex(random_bytes(16)));

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            }),
            [
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
