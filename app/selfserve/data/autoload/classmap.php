<?php

$rootPath = realpath(__DIR__ . '/../../');

return array(
    'Common\Service\Common\Form\Element\DynamicMultiCheckboxCommonService' => false,
    'Common\Service\Common\Form\Element\DynamicRadioCommonService' => false,
    'Common\Service\Common\Form\Element\DynamicSelectCommonService' => false,
    'Common\Service\Common\Form\Elements\Custom\OlcsCheckboxCommonService' => false,
    'Common\Service\DoctrineModule\Form\Element\ObjectMultiCheckboxDoctrineModuleService' => false,
    'Common\Service\DoctrineModule\Form\Element\ObjectRadioDoctrineModuleService' => false,
    'Common\Service\DoctrineModule\Form\Element\ObjectSelectDoctrineModuleService' => false,
    'DoctrineModule\Module' => $rootPath . '/vendor/doctrine/doctrine-module/src/DoctrineModule/Module.php',
    'DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory' => $rootPath . '/vendor/doctrine/doctrine-module/src'
        . '/DoctrineModule/ServiceFactory/AbstractDoctrineServiceFactory.php',
    'DoctrineORMModule\Module' => $rootPath . '/vendor/doctrine/doctrine-orm-module/src/DoctrineORMModule/Module.php',
    'Doctrine\Common\Annotations\AnnotationRegistry' => $rootPath . '/vendor/doctrine/annotations/lib/Doctrine/Common'
        . '/Annotations/AnnotationRegistry.php',
    'Mockery' => $rootPath . '/vendor/mockery/mockery/library/Mockery.php',
    'Mockery\Adapter\Phpunit\MockeryTestCase' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Adapter/Phpunit'
        . '/MockeryTestCase.php',
    'Mockery\CompositeExpectation' => $rootPath . '/vendor/mockery/mockery/library/Mockery/CompositeExpectation.php',
    'Mockery\Configuration' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Configuration.php',
    'Mockery\Container' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Container.php',
    'Mockery\CountValidator\CountValidatorAbstract' => $rootPath . '/vendor/mockery/mockery/library/Mockery'
        . '/CountValidator/CountValidatorAbstract.php',
    'Mockery\CountValidator\Exact' => $rootPath . '/vendor/mockery/mockery/library/Mockery/CountValidator/Exact.php',
    'Mockery\Expectation' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Expectation.php',
    'Mockery\ExpectationDirector' => $rootPath . '/vendor/mockery/mockery/library/Mockery/ExpectationDirector.php',
    'Mockery\ExpectationInterface' => $rootPath . '/vendor/mockery/mockery/library/Mockery/ExpectationInterface.php',
    'Mockery\Generator\CachingGenerator' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/CachingGenerator.php',
    'Mockery\Generator\DefinedTargetClass' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/DefinedTargetClass.php',
    'Mockery\Generator\Generator' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator/Generator.php',
    'Mockery\Generator\Method' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator/Method.php',
    'Mockery\Generator\MockConfiguration' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/MockConfiguration.php',
    'Mockery\Generator\MockConfigurationBuilder' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/MockConfigurationBuilder.php',
    'Mockery\Generator\MockDefinition' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/MockDefinition.php',
    'Mockery\Generator\Parameter' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator/Parameter.php',
    'Mockery\Generator\StringManipulationGenerator' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/StringManipulationGenerator.php',
    'Mockery\Generator\StringManipulation\Pass\CallTypeHintPass' => $rootPath . '/vendor/mockery/mockery/library'
        . '/Mockery/Generator/StringManipulation/Pass/CallTypeHintPass.php',
    'Mockery\Generator\StringManipulation\Pass\ClassNamePass' => $rootPath . '/vendor/mockery/mockery/library/Mockery'
        . '/Generator/StringManipulation/Pass/ClassNamePass.php',
    'Mockery\Generator\StringManipulation\Pass\ClassPass' => $rootPath . '/vendor/mockery/mockery/library/Mockery'
        . '/Generator/StringManipulation/Pass/ClassPass.php',
    'Mockery\Generator\StringManipulation\Pass\InstanceMockPass' => $rootPath . '/vendor/mockery/mockery/library'
        . '/Mockery/Generator/StringManipulation/Pass/InstanceMockPass.php',
    'Mockery\Generator\StringManipulation\Pass\InterfacePass' => $rootPath . '/vendor/mockery/mockery/library/Mockery'
        . '/Generator/StringManipulation/Pass/InterfacePass.php',
    'Mockery\Generator\StringManipulation\Pass\MethodDefinitionPass' => $rootPath . '/vendor/mockery/mockery/library'
        . '/Mockery/Generator/StringManipulation/Pass/MethodDefinitionPass.php',
    'Mockery\Generator\StringManipulation\Pass\Pass' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/StringManipulation/Pass/Pass.php',
    'Mockery\Generator\StringManipulation\Pass\RemoveBuiltinMethodsThatAreFinalPass' => $rootPath . '/vendor/mockery'
        . '/mockery/library/Mockery/Generator/StringManipulation/Pass/RemoveBuiltinMethodsThatAreFinalPass.php',
    'Mockery\Generator\StringManipulation\Pass\RemoveUnserializeForInternalSerializableClassesPass' => $rootPath . ''
        . '/vendor/mockery/mockery/library/Mockery/Generator/StringManipulation/Pass'
        . '/RemoveUnserializeForInternalSerializableClassesPass.php',
    'Mockery\Generator\UndefinedTargetClass' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Generator'
        . '/UndefinedTargetClass.php',
    'Mockery\Loader\EvalLoader' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Loader/EvalLoader.php',
    'Mockery\Loader\Loader' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Loader/Loader.php',
    'Mockery\Matcher\MatcherAbstract' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Matcher'
        . '/MatcherAbstract.php',
    'Mockery\Matcher\Type' => $rootPath . '/vendor/mockery/mockery/library/Mockery/Matcher/Type.php',
    'Mockery\MethodCall' => $rootPath . '/vendor/mockery/mockery/library/Mockery/MethodCall.php',
    'Mockery\MockInterface' => $rootPath . '/vendor/mockery/mockery/library/Mockery/MockInterface.php',
    'Mockery\ReceivedMethodCalls' => $rootPath . '/vendor/mockery/mockery/library/Mockery/ReceivedMethodCalls.php',
    'OlcsTest\Controller\Lva\AbstractLvaControllerTestCase' => $rootPath . '/test/Olcs/src/Controller/Lva'
        . '/AbstractLvaControllerTestCase.php',
    'Olcs\Logging\Module' => $rootPath . '/vendor/olcs/olcs-logging/src/Module.php',
    'Olcs\Module' => false,
    'Olcs\TestHelpers\Lva\Traits\LvaControllerTestTrait' => $rootPath . '/vendor/olcs/olcs-testhelpers/src/TestHelpers'
        . '/Lva/Traits/LvaControllerTestTrait.php',
    'Zend\Code\Annotation\AnnotationInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Code'
        . '/Annotation/AnnotationInterface.php',
    'Zend\Code\Annotation\AnnotationManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Code'
        . '/Annotation/AnnotationManager.php',
    'Zend\Code\Annotation\Parser\GenericAnnotationParser' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/Code/Annotation/Parser/GenericAnnotationParser.php',
    'Zend\Code\Annotation\Parser\ParserInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Code'
        . '/Annotation/Parser/ParserInterface.php',
    'Zend\Config\Factory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Config/Factory.php',
    'Zend\Di\DefinitionList' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di/DefinitionList.php',
    'Zend\Di\Definition\Annotation\Inject' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di'
        . '/Definition/Annotation/Inject.php',
    'Zend\Di\Definition\DefinitionInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di'
        . '/Definition/DefinitionInterface.php',
    'Zend\Di\Definition\IntrospectionStrategy' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di'
        . '/Definition/IntrospectionStrategy.php',
    'Zend\Di\Definition\RuntimeDefinition' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di'
        . '/Definition/RuntimeDefinition.php',
    'Zend\Di\DependencyInjectionInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di'
        . '/DependencyInjectionInterface.php',
    'Zend\Di\Di' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di/Di.php',
    'Zend\Di\InstanceManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di/InstanceManager.php',
    'Zend\Di\LocatorInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Di'
        . '/LocatorInterface.php',
    'Zend\EventManager\Event' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/EventManager/Event.php',
    'Zend\EventManager\EventInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/EventManager'
        . '/EventInterface.php',
    'Zend\EventManager\EventManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/EventManager'
        . '/EventManager.php',
    'Zend\EventManager\EventManagerAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/EventManagerAwareInterface.php',
    'Zend\EventManager\EventManagerInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/EventManagerInterface.php',
    'Zend\EventManager\EventsCapableInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/EventsCapableInterface.php',
    'Zend\EventManager\ListenerAggregateInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/ListenerAggregateInterface.php',
    'Zend\EventManager\ResponseCollection' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/ResponseCollection.php',
    'Zend\EventManager\SharedEventAggregateAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/EventManager/SharedEventAggregateAwareInterface.php',
    'Zend\EventManager\SharedEventManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/SharedEventManager.php',
    'Zend\EventManager\SharedEventManagerAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/EventManager/SharedEventManagerAwareInterface.php',
    'Zend\EventManager\SharedEventManagerInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/SharedEventManagerInterface.php',
    'Zend\EventManager\StaticEventManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/EventManager/StaticEventManager.php',
    'Zend\Filter\AbstractFilter' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter'
        . '/AbstractFilter.php',
    'Zend\Filter\FilterChain' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter/FilterChain.php',
    'Zend\Filter\FilterInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter'
        . '/FilterInterface.php',
    'Zend\Filter\FilterPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter'
        . '/FilterPluginManager.php',
    'Zend\Filter\Word\AbstractSeparator' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter/Word'
        . '/AbstractSeparator.php',
    'Zend\Filter\Word\SeparatorToCamelCase' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter'
        . '/Word/SeparatorToCamelCase.php',
    'Zend\Filter\Word\SeparatorToSeparator' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter'
        . '/Word/SeparatorToSeparator.php',
    'Zend\Filter\Word\UnderscoreToCamelCase' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter'
        . '/Word/UnderscoreToCamelCase.php',
    'Zend\Filter\Word\UnderscoreToDash' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Filter/Word'
        . '/UnderscoreToDash.php',
    'Zend\Form\Element' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form/Element.php',
    'Zend\Form\ElementAttributeRemovalInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/ElementAttributeRemovalInterface.php',
    'Zend\Form\ElementInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/ElementInterface.php',
    'Zend\Form\ElementPrepareAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/ElementPrepareAwareInterface.php',
    'Zend\Form\Factory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form/Factory.php',
    'Zend\Form\Fieldset' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form/Fieldset.php',
    'Zend\Form\FieldsetInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/FieldsetInterface.php',
    'Zend\Form\Form' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form/Form.php',
    'Zend\Form\FormAbstractServiceFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/FormAbstractServiceFactory.php',
    'Zend\Form\FormElementManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/FormElementManager.php',
    'Zend\Form\FormFactoryAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/FormFactoryAwareInterface.php',
    'Zend\Form\FormInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form/FormInterface.php',
    'Zend\Form\LabelAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form'
        . '/LabelAwareInterface.php',
    'Zend\Form\View\HelperConfig' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Form/View'
        . '/HelperConfig.php',
    'Zend\Http\AbstractMessage' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Http'
        . '/AbstractMessage.php',
    'Zend\Http\Headers' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Http/Headers.php',
    'Zend\Http\Request' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Http/Request.php',
    'Zend\Http\Response' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Http/Response.php',
    'Zend\I18n\View\HelperConfig' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/I18n/View'
        . '/HelperConfig.php',
    'Zend\InputFilter\BaseInputFilter' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/InputFilter'
        . '/BaseInputFilter.php',
    'Zend\InputFilter\EmptyContextInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/InputFilter/EmptyContextInterface.php',
    'Zend\InputFilter\Input' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/InputFilter/Input.php',
    'Zend\InputFilter\InputFilter' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/InputFilter'
        . '/InputFilter.php',
    'Zend\InputFilter\InputFilterInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/InputFilter/InputFilterInterface.php',
    'Zend\InputFilter\InputFilterPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/InputFilter/InputFilterPluginManager.php',
    'Zend\InputFilter\InputInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/InputFilter'
        . '/InputInterface.php',
    'Zend\InputFilter\ReplaceableInputInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/InputFilter/ReplaceableInputInterface.php',
    'Zend\InputFilter\UnknownInputsCapableInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/InputFilter/UnknownInputsCapableInterface.php',
    'Zend\Loader\AutoloaderFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Loader'
        . '/AutoloaderFactory.php',
    'Zend\Loader\ModuleAutoloader' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Loader'
        . '/ModuleAutoloader.php',
    'Zend\Loader\StandardAutoloader' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Loader'
        . '/StandardAutoloader.php',
    'Zend\Log\LoggerAbstractServiceFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Log'
        . '/LoggerAbstractServiceFactory.php',
    'Zend\Log\LoggerAwareTrait' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Log'
        . '/LoggerAwareTrait.php',
    'Zend\Log\ProcessorPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Log'
        . '/ProcessorPluginManager.php',
    'Zend\Log\WriterPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Log'
        . '/WriterPluginManager.php',
    'Zend\ModuleManager\Feature\BootstrapListenerInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/ModuleManager/Feature/BootstrapListenerInterface.php',
    'Zend\ModuleManager\Feature\ConfigProviderInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/ModuleManager/Feature/ConfigProviderInterface.php',
    'Zend\ModuleManager\Feature\ControllerProviderInterface' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/ModuleManager/Feature/ControllerProviderInterface.php',
    'Zend\ModuleManager\Feature\DependencyIndicatorInterface' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/ModuleManager/Feature/DependencyIndicatorInterface.php',
    'Zend\ModuleManager\Feature\InitProviderInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Feature/InitProviderInterface.php',
    'Zend\ModuleManager\Listener\AbstractListener' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/AbstractListener.php',
    'Zend\ModuleManager\Listener\AutoloaderListener' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/AutoloaderListener.php',
    'Zend\ModuleManager\Listener\ConfigListener' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/ConfigListener.php',
    'Zend\ModuleManager\Listener\ConfigMergerInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/ModuleManager/Listener/ConfigMergerInterface.php',
    'Zend\ModuleManager\Listener\DefaultListenerAggregate' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/ModuleManager/Listener/DefaultListenerAggregate.php',
    'Zend\ModuleManager\Listener\InitTrigger' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/InitTrigger.php',
    'Zend\ModuleManager\Listener\ListenerOptions' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/ListenerOptions.php',
    'Zend\ModuleManager\Listener\LocatorRegistrationListener' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/ModuleManager/Listener/LocatorRegistrationListener.php',
    'Zend\ModuleManager\Listener\ModuleDependencyCheckerListener' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/ModuleManager/Listener/ModuleDependencyCheckerListener.php',
    'Zend\ModuleManager\Listener\ModuleLoaderListener' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/ModuleLoaderListener.php',
    'Zend\ModuleManager\Listener\ModuleResolverListener' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/ModuleManager/Listener/ModuleResolverListener.php',
    'Zend\ModuleManager\Listener\OnBootstrapListener' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/OnBootstrapListener.php',
    'Zend\ModuleManager\Listener\ServiceListener' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/Listener/ServiceListener.php',
    'Zend\ModuleManager\Listener\ServiceListenerInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/ModuleManager/Listener/ServiceListenerInterface.php',
    'Zend\ModuleManager\ModuleEvent' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/ModuleManager'
        . '/ModuleEvent.php',
    'Zend\ModuleManager\ModuleManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/ModuleManager'
        . '/ModuleManager.php',
    'Zend\ModuleManager\ModuleManagerInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ModuleManager/ModuleManagerInterface.php',
    'Zend\Mvc\Controller\AbstractActionController' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Controller/AbstractActionController.php',
    'Zend\Mvc\Controller\AbstractController' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Controller/AbstractController.php',
    'Zend\Mvc\Controller\ControllerManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Controller/ControllerManager.php',
    'Zend\Mvc\Controller\PluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc/Controller'
        . '/PluginManager.php',
    'Zend\Mvc\Controller\Plugin\AbstractPlugin' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Controller/Plugin/AbstractPlugin.php',
    'Zend\Mvc\Controller\Plugin\PluginInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Controller/Plugin/PluginInterface.php',
    'Zend\Mvc\Controller\Plugin\Url' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc/Controller'
        . '/Plugin/Url.php',
    'Zend\Mvc\InjectApplicationEventInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/InjectApplicationEventInterface.php',
    'Zend\Mvc\MvcEvent' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc/MvcEvent.php',
    'Zend\Mvc\Router\RoutePluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc/Router'
        . '/RoutePluginManager.php',
    'Zend\Mvc\Service\AbstractPluginManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/Mvc/Service/AbstractPluginManagerFactory.php',
    'Zend\Mvc\Service\ConfigFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc/Service'
        . '/ConfigFactory.php',
    'Zend\Mvc\Service\ControllerLoaderFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/ControllerLoaderFactory.php',
    'Zend\Mvc\Service\ControllerPluginManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/Mvc/Service/ControllerPluginManagerFactory.php',
    'Zend\Mvc\Service\EventManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc/Service'
        . '/EventManagerFactory.php',
    'Zend\Mvc\Service\FilterManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/FilterManagerFactory.php',
    'Zend\Mvc\Service\FormElementManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/FormElementManagerFactory.php',
    'Zend\Mvc\Service\HydratorManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/HydratorManagerFactory.php',
    'Zend\Mvc\Service\InputFilterManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/InputFilterManagerFactory.php',
    'Zend\Mvc\Service\LogProcessorManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/LogProcessorManagerFactory.php',
    'Zend\Mvc\Service\LogWriterManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/LogWriterManagerFactory.php',
    'Zend\Mvc\Service\ModuleManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/ModuleManagerFactory.php',
    'Zend\Mvc\Service\RoutePluginManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/RoutePluginManagerFactory.php',
    'Zend\Mvc\Service\SerializerAdapterPluginManagerFactory' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/Mvc/Service/SerializerAdapterPluginManagerFactory.php',
    'Zend\Mvc\Service\ServiceListenerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/ServiceListenerFactory.php',
    'Zend\Mvc\Service\ServiceManagerConfig' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/ServiceManagerConfig.php',
    'Zend\Mvc\Service\ValidatorManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/ValidatorManagerFactory.php',
    'Zend\Mvc\Service\ViewHelperManagerFactory' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Mvc'
        . '/Service/ViewHelperManagerFactory.php',
    'Zend\Navigation\View\HelperConfig' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Navigation'
        . '/View/HelperConfig.php',
    'Zend\Serializer\AdapterPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Serializer'
        . '/AdapterPluginManager.php',
    'Zend\ServiceManager\AbstractFactoryInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/AbstractFactoryInterface.php',
    'Zend\ServiceManager\AbstractPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/AbstractPluginManager.php',
    'Zend\ServiceManager\Config' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/ServiceManager'
        . '/Config.php',
    'Zend\ServiceManager\ConfigInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/ConfigInterface.php',
    'Zend\ServiceManager\DelegatorFactoryInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/DelegatorFactoryInterface.php',
    'Zend\ServiceManager\FactoryInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/FactoryInterface.php',
    'Zend\ServiceManager\ServiceLocatorAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/ServiceLocatorAwareInterface.php',
    'Zend\ServiceManager\ServiceLocatorAwareTrait' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/ServiceLocatorAwareTrait.php',
    'Zend\ServiceManager\ServiceLocatorInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/ServiceLocatorInterface.php',
    'Zend\ServiceManager\ServiceManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/ServiceManager/ServiceManager.php',
    'Zend\Stdlib\AbstractOptions' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/AbstractOptions.php',
    'Zend\Stdlib\ArrayUtils' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib/ArrayUtils.php',
    'Zend\Stdlib\CallbackHandler' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/CallbackHandler.php',
    'Zend\Stdlib\DispatchableInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/DispatchableInterface.php',
    'Zend\Stdlib\ErrorHandler' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/ErrorHandler.php',
    'Zend\Stdlib\Extractor\ExtractionInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/Extractor/ExtractionInterface.php',
    'Zend\Stdlib\Glob' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib/Glob.php',
    'Zend\Stdlib\Hydrator\HydrationInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/Hydrator/HydrationInterface.php',
    'Zend\Stdlib\Hydrator\HydratorInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/Hydrator/HydratorInterface.php',
    'Zend\Stdlib\Hydrator\HydratorPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/Stdlib/Hydrator/HydratorPluginManager.php',
    'Zend\Stdlib\InitializableInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/InitializableInterface.php',
    'Zend\Stdlib\Message' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib/Message.php',
    'Zend\Stdlib\MessageInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/MessageInterface.php',
    'Zend\Stdlib\ParameterObjectInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/ParameterObjectInterface.php',
    'Zend\Stdlib\ParametersInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/ParametersInterface.php',
    'Zend\Stdlib\PriorityQueue' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/PriorityQueue.php',
    'Zend\Stdlib\RequestInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/RequestInterface.php',
    'Zend\Stdlib\ResponseInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/ResponseInterface.php',
    'Zend\Stdlib\SplPriorityQueue' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib'
        . '/SplPriorityQueue.php',
    'Zend\Stdlib\StringUtils' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Stdlib/StringUtils.php',
    'Zend\Uri\Http' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Uri/Http.php',
    'Zend\Uri\Uri' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Uri/Uri.php',
    'Zend\Uri\UriInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Uri/UriInterface.php',
    'Zend\Validator\AbstractValidator' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Validator'
        . '/AbstractValidator.php',
    'Zend\Validator\Translator\TranslatorAwareInterface' => $rootPath . '/vendor/zendframework/zendframework/library'
        . '/Zend/Validator/Translator/TranslatorAwareInterface.php',
    'Zend\Validator\Translator\TranslatorInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/Validator/Translator/TranslatorInterface.php',
    'Zend\Validator\ValidatorChain' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Validator'
        . '/ValidatorChain.php',
    'Zend\Validator\ValidatorInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Validator'
        . '/ValidatorInterface.php',
    'Zend\Validator\ValidatorPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/Validator'
        . '/ValidatorPluginManager.php',
    'Zend\View\HelperPluginManager' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View'
        . '/HelperPluginManager.php',
    'Zend\View\Helper\AbstractHelper' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View/Helper'
        . '/AbstractHelper.php',
    'Zend\View\Helper\HeadScript' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View/Helper'
        . '/HeadScript.php',
    'Zend\View\Helper\HelperInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View/Helper'
        . '/HelperInterface.php',
    'Zend\View\Helper\InlineScript' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View/Helper'
        . '/InlineScript.php',
    'Zend\View\Helper\Placeholder\Container' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View'
        . '/Helper/Placeholder/Container.php',
    'Zend\View\Helper\Placeholder\Container\AbstractContainer' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/View/Helper/Placeholder/Container/AbstractContainer.php',
    'Zend\View\Helper\Placeholder\Container\AbstractStandalone' => $rootPath . '/vendor/zendframework/zendframework'
        . '/library/Zend/View/Helper/Placeholder/Container/AbstractStandalone.php',
    'Zend\View\Model\ClearableModelInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View'
        . '/Model/ClearableModelInterface.php',
    'Zend\View\Model\ModelInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View/Model'
        . '/ModelInterface.php',
    'Zend\View\Model\RetrievableChildrenInterface' => $rootPath . '/vendor/zendframework/zendframework/library/Zend'
        . '/View/Model/RetrievableChildrenInterface.php',
    'Zend\View\Model\ViewModel' => $rootPath . '/vendor/zendframework/zendframework/library/Zend/View/Model'
        . '/ViewModel.php',
    'demeter_get' => false,
    'org\bovigo\vfs\DotDirectory' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/DotDirectory.php',
    'org\bovigo\vfs\Quota' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs/Quota.php',
    'org\bovigo\vfs\content\FileContent' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs/content'
        . '/FileContent.php',
    'org\bovigo\vfs\content\SeekableFileContent' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/content/SeekableFileContent.php',
    'org\bovigo\vfs\content\StringBasedFileContent' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo'
        . '/vfs/content/StringBasedFileContent.php',
    'org\bovigo\vfs\vfsStream' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStream.php',
    'org\bovigo\vfs\vfsStreamAbstractContent' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamAbstractContent.php',
    'org\bovigo\vfs\vfsStreamContainer' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamContainer.php',
    'org\bovigo\vfs\vfsStreamContainerIterator' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamContainerIterator.php',
    'org\bovigo\vfs\vfsStreamContent' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamContent.php',
    'org\bovigo\vfs\vfsStreamDirectory' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamDirectory.php',
    'org\bovigo\vfs\vfsStreamFile' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamFile.php',
    'org\bovigo\vfs\vfsStreamWrapper' => $rootPath . '/vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs'
        . '/vfsStreamWrapper.php',
);
