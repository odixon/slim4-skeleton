<?php

namespace App\Action\Login;

use App\Domain\User\Data\UserAuthData;
use App\Domain\User\Service\UserAuth;
use App\Utility\Redirector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Selective\SlimHelper\ResponseHelper;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Action.
 */
final class LoginSubmitAction
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UserAuth
     */
    private $auth;

    /**
     * The constructor.
     *
     * @param Session $session The session handler
     * @param UserAuth $auth The user auth
     */
    public function __construct(Session $session, UserAuth $auth)
    {
        $this->session = $session;
        $this->auth = $auth;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array)$request->getParsedBody();
        $username = (string)($data['username'] ?? '');
        $password = (string)($data['password'] ?? '');

        $user = $this->auth->authenticate($username, $password);

        $flash = $this->session->getFlashBag();
        $flash->clear();

        if ($user) {
            $this->startUserSession($user);
            $flash->set('success', __('Login successfully'));
            $url = 'user-list';
        } else {
            $flash->set('error', __('Login failed!'));
            $url = 'login';
        }

        return Redirector::redirect($request, $response, $url);
    }

    /**
     * Init user session.
     *
     * @param UserAuthData $user The user
     *
     * @return void
     */
    private function startUserSession(UserAuthData $user): void
    {
        // Clears all session data and regenerates session ID
        $this->session->invalidate();
        $this->session->start();

        $this->session->set('user', $user);

        // Store user settings in session
        $this->auth->setUser($user);
    }
}