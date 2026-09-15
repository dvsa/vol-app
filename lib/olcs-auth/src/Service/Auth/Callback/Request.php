<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Request
{
    public const STAGE_AUTHENTICATE = 'LDAP1';

    public const STAGE_EXPIRED_PASSWORD = 'LDAP2';

    /**
     * Create a request
     *
     * @param string                    $authId    Auth id
     * @param string                    $stage     Stage
     * @param CallbackInterface[]|array $callbacks Callbacks
     *
     * @return void
     */
    public function __construct(private $authId, private $stage, private array $callbacks = [])
    {
    }

    /**
     * Add callback
     *
     * @param CallbackInterface $callback Callback
     */
    public function addCallback(CallbackInterface $callback): void
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray()
    {
        $callbacks = [];

        /** @var CallbackInterface $callback */
        foreach ($this->callbacks as $callback) {
            $callbacks[] = $callback->toArray();
        }

        return [
            'authId' => $this->authId,
            'stage' => $this->stage,
            'callbacks' => $callbacks
        ];
    }
}
