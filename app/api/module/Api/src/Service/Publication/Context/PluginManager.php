<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @template-extends AbstractPluginManager<ContextInterface>
 */
class PluginManager extends AbstractPluginManager
{
    protected $instanceOf = ContextInterface::class;

    public function __construct(ContainerInterface $configuration = null)
    {
        parent::__construct($configuration);
        $this->addAbstractFactory(new AbstractFactory());
        $this->addInitializer(
            new AddressFormatterInitializer()
        );
    }
}
