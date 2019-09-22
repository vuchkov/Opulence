<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Framework\Authentication\Http\Middleware;

use Closure;
use Opulence\Authentication\Credentials\Authenticators\IAuthenticator;
use Opulence\Authentication\IAuthenticationContext;
use Opulence\Authentication\ISubject;
use Opulence\Authorization\IAuthority;
use Opulence\Framework\Authentication\Credentials\IHttpCredentialIO;
use Opulence\Http\HttpException;
use Opulence\Http\Requests\Request;
use Opulence\Http\Responses\Response;
use Opulence\Routing\Middleware\IMiddleware;

/**
 * Defines the authentication and authorization middleware
 */
class Authenticate implements IMiddleware
{
    /** @var IAuthenticator The authenticator */
    protected IAuthenticator $authenticator;
    /** @var IAuthenticationContext The authentication context */
    protected IAuthenticationContext $authenticationContext;
    /** @var IAuthority The authority */
    protected IAuthority $authority;
    /** @var IHttpCredentialIO The credential IO */
    protected IHttpCredentialIO $credentialIO;

    /**
     * @param IAuthenticator $authenticator The authenticator
     * @param IAuthenticationContext $authenticationContext The authentication context
     * @param IAuthority $authority The authority
     * @param IHttpCredentialIO $credentialIO The credential IO
     */
    public function __construct(
        IAuthenticator $authenticator,
        IAuthenticationContext $authenticationContext,
        IAuthority $authority,
        IHttpCredentialIO $credentialIO
    ) {
        $this->authenticator = $authenticator;
        $this->authenticationContext = $authenticationContext;
        $this->authority = $authority;
        $this->credentialIO = $credentialIO;
    }

    /**
     * @inheritdoc
     */
    public function handle(Request $request, Closure $next): Response
    {
        $credential = $this->credentialIO->read($request);

        if (!$this->authenticator->authenticate($credential, $subject)) {
            throw new HttpException(403);
        }

        /** @var ISubject $subject */
        $this->authenticationContext->setSubject($subject);
        $this->authority->setSubject($subject->getPrimaryPrincipal()->getId(), $subject->getRoles());

        return $next($request);
    }
}
