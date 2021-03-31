<?php
declare(strict_types = 1);

namespace Olcs\View\Helper\SessionTimeoutWarning;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class SessionTimeoutWarningFactory implements FactoryInterface
{
    /**
     * @var SessionTimeoutWarningFactoryConfigInputFilter
     */
    private $configInputFilter;

    /**
     * SessionTimeoutWarningFactory constructor.
     * @param SessionTimeoutWarningFactoryConfigInputFilter|null $configInputFilter
     */
    public function __construct(?SessionTimeoutWarningFactoryConfigInputFilter $configInputFilter = null)
    {
        if (is_null($configInputFilter)) {
            $configInputFilter = new SessionTimeoutWarningFactoryConfigInputFilter();
        }
        $this->configInputFilter = $configInputFilter;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SessionTimeoutWarning
     * @throws \Exception
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SessionTimeoutWarning
    {
        $config = $serviceLocator->getServiceLocator()->get('Config')['session-timeout-warning-modal-helper'] ?? [];
        $this->validateConfiguration($config);

        return new SessionTimeoutWarning(
            $this->configInputFilter->getValue(
                SessionTimeoutWarningFactoryConfigInputFilter::CONFIG_ENABLED
            ),
            $this->configInputFilter->getValue(
                SessionTimeoutWarningFactoryConfigInputFilter::CONFIG_SECONDS_BEFORE_EXPIRY_WARNING
            ),
            $this->configInputFilter->getValue(
                SessionTimeoutWarningFactoryConfigInputFilter::CONFIG_TIMEOUT_REDIRECT_URL
            )
        );
    }

    /**
     * Validates the configuration set.
     * @param array $config
     * @throws \Exception
     */
    private function validateConfiguration(array $config): void
    {
        $this->configInputFilter->setData($config);
        if (!$this->configInputFilter->isValid()) {
            throw new \Exception(
                "Unable to instantiate SessionTimeoutWarning due to invalid configuration: "
                . PHP_EOL
                . json_encode($this->configInputFilter->getMessages())
            );
        }
    }
}
