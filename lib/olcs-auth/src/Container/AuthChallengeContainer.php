<?php

namespace Dvsa\Olcs\Auth\Container;

use Laminas\Session\Container;

/**
 * Class AuthChallengeContainer
 *
 * @template-extends Container<string, string>
 */
class AuthChallengeContainer extends Container
{
    protected const CONTAINER_NAME = 'authChallenge';

    protected const KEY_CHALLENGE_NAME = 'challengeName';

    protected const KEY_CHALLENGE_SESSION = 'challengeSession';

    protected const KEY_CHALLENGE_IDENTITY = 'challengeIdentity';

    public const CHALLENEGE_NEW_PASWORD_REQUIRED = 'NEW_PASSWORD_REQUIRED';

    public function __construct()
    {
        parent::__construct(static::CONTAINER_NAME);
    }

    public function getChallengeSession(): string
    {
        return $this->offsetGet(static::KEY_CHALLENGE_SESSION);
    }

    public function setChallengeSession(string $challengeSession): AuthChallengeContainer
    {
        $this->offsetSet(static::KEY_CHALLENGE_SESSION, $challengeSession);
        return $this;
    }

    public function getChallengeName(): string
    {
        return $this->offsetGet(static::KEY_CHALLENGE_NAME);
    }

    public function setChallengeName(string $challengeName): AuthChallengeContainer
    {
        $this->offsetSet(static::KEY_CHALLENGE_NAME, $challengeName);
        return $this;
    }

    public function getChallengedIdentity(): string
    {
        return $this->offsetGet(static::KEY_CHALLENGE_IDENTITY);
    }

    /**
     * @param string $challengedIdentity :
     */
    public function setChallengedIdentity(string $challengedIdentity): AuthChallengeContainer
    {
        $this->offsetSet(static::KEY_CHALLENGE_IDENTITY, $challengedIdentity);
        return $this;
    }

    public function clear(): void
    {
        $this->exchangeArray([]);
    }
}
