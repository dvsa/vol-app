<?php

/**
 * Module configuration
 */
return array(
    'router' => array(
        'routes' => array(
//----- Start of routes ---------------------
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'OlcsSelfserve\Controller\Index',
                        'action' => 'index',
                    )
                )
            ),
            'business-type' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/selfserve/business-type',
                    'defaults' => array(
                        'controller' => 'OlcsSelfserve\Controller\BusinessType',
                        'action' => 'details',
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'details' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/[:applicationId]',
                            'constraints' => array(
                                'applicationId' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'controller' => 'OlcsSelfserve\Controller\BusinessType',
                                'action' => 'details'
                            )
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'OlcsSelfserve\Controller\Index' => 'OlcsSelfserve\Controller\IndexController',
            'OlcsSelfserve\Controller\BusinessType' => 'OlcsSelfserve\Controller\Selfserve\BusinessTypeController'
        )
    ),
    //-------- End of routes -----------------
    
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
    ),
    //-------- End navigation -----------------
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            // Keys are the service names.
            // Valid values include names of classes implementing
            // FactoryInterface, instances of classes implementing
            // FactoryInterface, or any PHP callbacks
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Zend\Log' => function ($sm) {
                $log = new Zend\Log\Logger();
                $writer = new Zend\Log\Writer\Stream('/var/log/olcs/olcs-selfserve.log');
                $log->addWriter($writer);
                return $log;
            },
        )
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            )
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'layout/popup' => __DIR__ . '/../view/layout/popup.phtml',
            'olcs/index/index' => __DIR__ . '/../view/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
           'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            )
        )
    ),
    // Doctrine driver config
    'doctrine' => array(
        'driver' => array(
            'Olcs_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Olcs/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Olcs\Entity' => 'Olcs_driver'
                )
            )
        )
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'DataListPlugin' => 'OlcsSelfserve\Controller\Plugin\DataListPlugin',
            'transactional' => 'OlcsSelfserve\Controller\Plugin\DoctrineTransaction',
        )
    ),
    'resource_strings' => __DIR__ . '/translations.ini',
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'olcs_client_details' => array(
        'client_id' => 'OLCS',
        'client_secret' => 'olcssecret'
    )
);
