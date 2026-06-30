<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ChangePasswordControllerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChangePasswordController
    {
        /** @var array $config */
        $config = $container->get('Config');
        $formHelper = $container->get('Helper\Form');
        $flashMessenger = $container->get('Helper\FlashMessenger');
        $commandSender = $container->get('CommandSender');
        $redirectPlugin = $container->get('ControllerPluginManager')->get('redirect');

        return new ChangePasswordController(
            $formHelper,
            $flashMessenger,
            $config,
            $commandSender,
            $redirectPlugin
        );
    }
}
