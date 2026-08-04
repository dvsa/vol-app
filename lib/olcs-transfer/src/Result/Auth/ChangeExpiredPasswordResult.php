<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Result\Auth;

use InvalidArgumentException;

class ChangeExpiredPasswordResult
{
    public const SUCCESS = 1;
    public const SUCCESS_WITH_CHALLENGE = 2;

    public const FAILURE = 0;
    public const FAILURE_NEW_PASSWORD_INVALID = -1;
    public const FAILURE_CLIENT_ERROR = -2;
    public const FAILURE_NOT_AUTHORIZED = -3;
    public const FAILURE_NEW_PASSWORD_MATCHES_OLD = -4;

    /**
     * @var int
     */
    protected int $code;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param array $identity
     */
    public function __construct(int $code, protected array $identity = [], protected array $messages = [], private array $options = [])
    {
        if (!$this->isValidCode($code)) {
            throw new InvalidArgumentException(sprintf("%d is not a valid code", $code));
        }

        $this->code     = $code;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return ($this->code > 0);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isValidCode(int $code): bool
    {
        $reflectionClass = new \ReflectionClass($this);
        foreach ($reflectionClass->getConstants() as $value) {
            if ($value === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ChangeExpiredPasswordResult
     */
    public static function fromArray(array $data): ChangeExpiredPasswordResult
    {
        return new self(
            $data['code'] ?? null,
            $data['identity'] ?? [],
            $data['messages'] ?? [],
            $data['options'] ?? []
        );
    }
}
