<?php

namespace Olcs\Controller\Factory\Licence;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Validator\Translator\TranslatorInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\ContinuationController;

class ContinuationControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ContinuationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ContinuationController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translator = $container->get('translator');

        $validatorOptions = [
            'inclusive' => true,
            'translator' => $translator,
            'message' => 'update-continuation.validation.total-auth-vehicles'
        ];

        /** @var LessThan $lessThanValidator */
        $lessThanValidator = $container->get('ValidatorManager')->get(LessThan::class);
        $lessThanValidator->setOptions($validatorOptions);


        return new ContinuationController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $lessThanValidator
        );
    }
}
