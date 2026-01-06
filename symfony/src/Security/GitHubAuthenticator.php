<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GitHubAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_github_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('github');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GithubResourceOwner $githubUser */
                $githubUser = $client->fetchUserFromToken($accessToken);

                $email = $githubUser->getEmail();
                if (!$email) {
                    $email = $githubUser->getId() . '@github.noreply.local';
                    $this->logger->warning('GitHub user has no verified email', [
                        'github_id' => $githubUser->getId(),
                        'github_username' => $githubUser->getNickname(),
                    ]);
                }

                $existingUser = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['githubId' => (string) $githubUser->getId()]);

                if ($existingUser) {
                    $existingUser->setName($githubUser->getName() ?? $githubUser->getNickname());
                    $existingUser->setEmail($email);
                    $existingUser->setGithubUsername($githubUser->getNickname());
                    $existingUser->setUpdatedAt(new \DateTime());
                    $this->entityManager->flush();

                    return $existingUser;
                }

                $user = new User();
                $user->setGithubId((string) $githubUser->getId());
                $user->setName($githubUser->getName() ?? $githubUser->getNickname());
                $user->setEmail($email);
                $user->setGithubUsername($githubUser->getNickname());

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->error('GitHub OAuth failed', ['error' => $exception->getMessage()]);

        return new RedirectResponse($this->router->generate('home'));
    }
}
