<?php

namespace App\Security;

use App\Service\BillingService;
use App\Exception\BillingUnavailableException;
use App\Service\BillingClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BillingAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private BillingClient $billingClient;

    public function __construct(UrlGeneratorInterface $urlGenerator, billingClient $billingClient)
    {
        $this->urlGenerator = $urlGenerator;
        $this->billingClient = $billingClient;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $csrfToken = $request->get('_csrf_token');

        if (!$email || !$password) {
            throw new CustomUserMessageAuthenticationException('Необходимо ввести email и пароль.');
        }

        $credentials = json_encode(['username' => $email, 'password' => $password]);

        $userLoader = fn() => $this->loadUser($credentials);

        return new SelfValidatingPassport(
            new UserBadge($email, $userLoader),
            [new CsrfTokenBadge('authenticate', $csrfToken)]
        );
    }

    private function loadUser(string $credentials): UserInterface
    {
        try {
            $response = $this->billingClient->autheticate($credentials);

            if (isset($response['code'])) {
                throw new BillingUnavailableException($response['message']);
            }

            $userResponse = $this->billingClient->getCurrentUser($response['token']);
        } catch (BillingUnavailableException | \JsonException $e) {
            throw new AuthenticationException('Произошла ошибка во время авторизации: ' . $e->getMessage());
        }

            $user = new User();
            $user->setApiToken($response['token']);
            $user->setRoles($userResponse['roles']);
            $user->setBalance($userResponse['balance']);
            $user->setEmail($userResponse['username']);

            return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        return $targetPath
            ? new RedirectResponse($targetPath)
            : new RedirectResponse($this->urlGenerator->generate('app_course_index'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}