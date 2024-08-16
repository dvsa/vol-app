<?php

namespace Dvsa\Olcs\Api\Rbac;

use Dvsa\Contracts\Auth\Exceptions\InvalidTokenException;
use Dvsa\Contracts\Auth\OAuthClientInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\HeaderNotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Cli\Request\CliRequest;
use Firebase\JWT\ExpiredException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Request;
use Laminas\Stdlib\RequestInterface;
use LmcRbacMvc\Identity\IdentityInterface;

/**
 * @see JWTIdentityProviderFactory
 */
class JWTIdentityProvider implements IdentityProviderInterface
{
    use IdentityProviderTrait;

    public const HEADER_NAME = 'Authorization';
    public const MESSAGE_MALFORMED_BEARER = 'Malformed Bearer token';

    /**
     * @var Identity;
     */
    private $identity;

    public function __construct(private UserRepository $repository, private RequestInterface $request, private OAuthClientInterface $client)
    {
    }

    /**
     * Get the identity
     *
     * @return IdentityInterface
     * @throws BadRequestException
     * @throws InvalidTokenException
     * @throws HeaderNotFoundException
     * @throws NotFoundException
     */
    public function getIdentity(): IdentityInterface
    {
        if (!is_null($this->identity)) {
            return $this->identity;
        }

        if ($this->request instanceof CliRequest) {
            return $this->identity = new Identity($this->getSystemUser());
        }

        if (empty($header = $this->request->getHeader(static::HEADER_NAME))) {
            return $this->identity = new Identity(User::anon());
        }

        try {
            $decodedToken = $this->getJWT($header);
        } catch (InvalidTokenException $exception) {
            if (!$exception->getPrevious() instanceof ExpiredException) {
                throw $exception;
            }
            // We only want to allow an ExpiredException to return an anon user as this then allows the RefreshTokens command to be handled
            return $this->identity = new Identity(User::anon());
        }
        $user = $this->repository->fetchEnabledIdentityByLoginId($decodedToken['username']);

        if (is_null($user)) {
            return $this->identity = new Identity(User::anon());
        }

        return $this->identity = new Identity($user);
    }

    /**
     * @return mixed
     * @throws NotFoundException
     */
    private function getSystemUser()
    {
        $auth = IdentityProviderInterface::SYSTEM_USER;
        return $this->repository->fetchById($auth);
    }

    /**
     * @return array
     * @throws BadRequestException
     * @throws InvalidTokenException
     */
    private function getJWT(HeaderInterface $header): array
    {
        if (!preg_match('/Bearer\s((.*)\.(.*)\.(.*))/', $header->getFieldValue())) {
            throw new BadRequestException(static::MESSAGE_MALFORMED_BEARER);
        }

        $token = substr($header->getFieldValue(), 7);

        return $this->client->decodeToken($token);
    }
}
