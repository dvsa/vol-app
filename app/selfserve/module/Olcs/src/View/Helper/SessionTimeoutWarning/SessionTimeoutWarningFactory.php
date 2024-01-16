<?php
declare(strict_types = 1);

namespace Olcs\View\Helper\SessionTimeoutWarning;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\factory\FactoryInterface;
use Laminas\View\Helper\HeadMeta;

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

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SessionTimeoutWarning
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SessionTimeoutWarning
    {
        $config = $container->get('Config')['session-timeout-warning-modal-helper'] ?? [];
        $this->validateConfiguration($config);
        return new SessionTimeoutWarning(
            new HeadMeta(),
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
}
