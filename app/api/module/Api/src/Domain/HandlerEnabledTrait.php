<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;
use Olcs\Logging\Log\Logger;

/**
 * HandlerEnabledTrait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait HandlerEnabledTrait
{
    /**
     * Check whether the handler is enabled, if not throw the exception
     *
     * @return bool
     * @throws DisabledHandlerException
     */
    public function checkEnabled(): bool
    {
        if (!$this->isEnabled()) {
            $fqdn = $this::class;
            $exception = new DisabledHandlerException($fqdn);
            Logger::warn($fqdn . ': ' . $exception->getMessage());
            throw $exception;
        }

        return true;
    }

    /**
     * Whether the handler is enabled (only checks handlers which require a toggle check)
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        //if handler doesn't implement the interface, we don't need to check
        if (!$this instanceof ToggleRequiredInterface) {
            return true;
        }

        //if we have a toggle config variable populated, test using this
        //a series of values can be passed, if any one of them is switched off then disable the controller
        if (!empty($this->toggleConfig)) {
            foreach ($this->toggleConfig as $toggle) {
                if (!$this->getToggleService()->isEnabled($toggle)) {
                    return false;
                }
            }

            return true;
        }

        //if no toggle config is set, test using the handler name instead
        $handlerName = $this->shortFqdn();
        return $this->getToggleService()->isEnabled($handlerName);
    }

    public function shortFqdn(?string $fqdn = null): string
    {
        if ($fqdn === null) {
            $fqdn = static::class;
        }

        return str_replace('Dvsa\Olcs\Api\Domain\\', '', $fqdn);
    }
}
