<?php

declare(strict_types=1);

namespace Common\Auth\Service;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Auth\RefreshTokens;
use Exception;
use Laminas\Authentication\Storage\Session;

class RefreshTokenService
{
    public const MESSAGE_BASE = "JWT refresh failed: %s";

    public const MESSAGE_RESULT_NOT_OK = 'Result is not ok';

    public const MESSAGE_AUTH_RESULT_NOT_VALID = 'Result is not valid';

    public const MESSAGE_IDENTITY_MISSING = 'Result is missing new identity';

    public const EXPIRES_WITHIN_SECONDS = 60;

    protected Session $session;

    /**
     * RefreshTokenService constructor.
     */
    public function __construct(protected CommandSender $commandSender)
    {
    }

    /**
     * @throws Exception
     */
    public function refreshTokens(array $tokens, string $identifier): array
    {
        $refreshCommand = RefreshTokens::create([
            'refreshToken' => $tokens['refresh_token'],
            'username' => $identifier
        ]);

        $result = $this->commandSender->send($refreshCommand);

        if (!$result->isOk()) {
            throw new Exception(sprintf(static::MESSAGE_BASE, static::MESSAGE_RESULT_NOT_OK));
        }

        $flags = $result->getResult()['flags'];
        if (!$flags['isValid']) {
            throw new Exception(sprintf(static::MESSAGE_BASE, static::MESSAGE_AUTH_RESULT_NOT_VALID));
        }

        $identity = $flags['identity'] ?? null;
        if (empty($identity)) {
            throw new Exception(sprintf(static::MESSAGE_BASE, static::MESSAGE_IDENTITY_MISSING));
        }

        return $identity;
    }

    public function isRefreshRequired(array $token): bool
    {
        return $token['expires'] - time() < static::EXPIRES_WITHIN_SECONDS;
    }
}
