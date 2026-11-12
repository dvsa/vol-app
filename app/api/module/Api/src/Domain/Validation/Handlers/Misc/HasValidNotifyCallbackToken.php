<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\Notify\UpdateDeliveryStatus;
use Psr\Container\ContainerInterface;

/**
 * Checks the bearer token GOV.UK Notify sent with a delivery status callback
 */
class HasValidNotifyCallbackToken extends AbstractHandler
{
    private string $expectedToken = '';

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
        $this->setExpectedToken((string) ($config['notify']['callback_token'] ?? ''));

        return parent::__invoke($container, $requestedName, $options);
    }

    public function setExpectedToken(string $expectedToken): void
    {
        $this->expectedToken = trim($expectedToken);
    }

    /**
     * @param UpdateDeliveryStatus $dto DTO being validated
     *
     * @return bool
     */
    #[\Override]
    public function isValid($dto)
    {
        // blank, " " and "null" mean the token isn't set up for this environment
        if (in_array($this->expectedToken, ['', 'null'], true)) {
            return false;
        }

        return hash_equals($this->expectedToken, (string) $dto->getToken());
    }
}
