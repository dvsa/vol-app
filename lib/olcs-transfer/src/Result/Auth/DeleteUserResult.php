<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Result\Auth;

use InvalidArgumentException;

class DeleteUserResult
{
    public const SUCCESS = 1;

    public const FAILURE = 0;
    public const FAILURE_USER_NOT_FOUND = -1;

    public const MESSAGE_SUCCESS = 'User deleted';
    public const MESSAGE_FAILURE = 'Failed to delete user';
    public const MESSAGE_FAILURE_NOT_FOUND = 'User not deleted (not present in Cognito)';

    private int $code;

    public function __construct(int $code, private ?string $message = null)
    {
        if (!$this->isValidCode($code)) {
            throw new InvalidArgumentException(sprintf("%d is not a valid code", $code));
        }

        $this->code = $code;
    }

    public function isValid(): bool
    {
        return ($this->code === static::SUCCESS);
    }

    public function isUserNotPresent(): bool
    {
        return ($this->code === static::FAILURE_USER_NOT_FOUND);
    }

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
     * @codeCoverageIgnore
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    public static function fromArray(array $data): DeleteUserResult
    {
        return new self(
            $data['code'] ?? null,
            $data['message'] ?? null,
        );
    }
}
