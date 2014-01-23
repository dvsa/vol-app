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
                        'controller' => 'Olcs\Controller\Index',
                        'action' => 'index',
                    )
                )
            ),
            'welcome' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/welcome',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Index',
                        'action' => 'welcome',
                    )
                )
            ),
            'lookup' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/search',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Lookup',
                        'action' => 'index',
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'searchActions' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/[:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z0-9_-]+'
                            ),
                            'defaults' => array(
                                'action' => 'index'
                            )
                        )
                    )
                )
            ),

            'lookup_operator' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/search/operator-results',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Lookup',
                        'action' => 'operatorResults',
                    )
                ),
            ),
            'operator_results' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/search/operator-results[/][page][/:page][/][:s]',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Lookup',
                        'action' => 'operatorResults',
                    )
                ),
            ),

             'casenotes' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/note',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\CaseNotes',
                        'action' => 'index',
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'searchActions' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/[:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z0-9_-]+'
                            ),
                            'defaults' => array(
                                'action' => 'index'
                            )
                        )
                    )
                )
            ),
            'case_new' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/case/new/:licenceId[/]',
                    'constraints' => array(
                        'licenceId'  => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\New',
                        'action' => 'index',
                    )
                )
            ),
             'case_done' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/done',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\New',
                        'action' => 'done',
                    )
                )
            ),
            'case_list' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/case/list/:licenceId[/][page][/:page][/:s]',
                    'constraints' => array(
                        'licenceId'  => '[0-9]+',
                        'page'  => '[0-9]+',
                        's'  => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\List',
                        'action' => 'index',
                    )
                )
            ),
            'case_list_ajax' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/ajax-case-list',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\List',
                        'action' => 'ajaxCaseList',
                    )
                )
            ),
            'case_dashboard' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/case/:licenceId/:caseId/dashboard',
                    'constraints' => array(
                        'licenceId'  => '[0-9]+',
                        'caseId'  => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Dashboard',
                        'action' => 'index',
                    )
                )
            ),
            'case_dashboard_post' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/dashboard',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Dashboard',
                        'action' => 'formPost',
                    )
                )
            ),
            'case_dashboard_ajax' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/ajax-case-dashboard',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Dashboard',
                        'action' => 'ajaxCaseDashboard',
                    )
                )
            ),
            'case_convictions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/case/:licenceId/:caseId/convictions',
                    'constraints' => array(
                        'licenceId'  => '[0-9]+',
                        'caseId'  => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Convictions',
                        'action' => 'index',
                    )
                )
            ),
            'case_convictions_post' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/convictions',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Convictions',
                        'action' => 'formPost',
                    )
                )
            ),
            'case_ajax-conviction-list-sort' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/ajax-conviction-list-sort',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Convictions',
                        'action' => 'ajaxConvictionListSort',
                    )
                )
            ),
            'ajax_case_notes' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/notes',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\CaseNotes',
                        'action' => 'ajaxDetailSave',
                    )
                )
            ),
            'ajax_case_dashboard_get' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/ajax-dashboard-get',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Convictions',
                        'action' => 'ajaxDashboardGet',
                    )
                )
            ),
            'ajax_case_details_save' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/ajax-detail-save',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Convictions',
                        'action' => 'ajaxDetailSave',
                    )
                )
            ),
            'case_submission_generator' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/submission/generate',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Submission',
                        'action' => 'generator',
                    )
                )
            ),
            'case_submission_view' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/submission',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Submission',
                        'action' => 'view',
                    )
                )
            ),
             'case_submission_send' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/submission/send',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Submission',
                        'action' => 'send',
                    )
                )
            ),
            'ajax_submission_section_retrieval' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/case/submission/section',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\VCase\Submission',
                        'action' => 'ajaxSectionRetrieval',
                    )
                )
            ),
            'application_new' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/new',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\New',
                        'action' => 'index',
                    )
                )
            ),
            'application_new_details' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/new/details',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\New',
                        'action' => 'details',
                    )
                )
            ),
            
            
            'application_search_operator' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/search/operator',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Search',
                        'action' => 'operator',
                    )
                )
            ),
            'application_operator_search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/search/operator/:operatorName[/][type][/:entityType][/][page][/:page][/:s]',
                    'constraints' => array(
                        'page'  => '[0-9]+',
                        's'  => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Search',
                        'action' => 'operator',
                    )
                )
            ),
            'application_operator_edit' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/search/editoperator',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Search',
                        'action' => 'operatoredit',
                    )
                )
            ),
            'application_operator_search_ajax' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/application/search/operator/ajax',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Search',
                        'action' => 'ajaxOperatorSearch',
                    )
                )
            ),
            'application_person_search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/search/person[/:page][/:s]',
                    'constraints' => array(
                        'page'  => '[0-9]+',
                        's'  => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Person',
                        'action' => 'search',
                    )
                )
            ),
            'application_subsidiary_search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/search/subsidiary[/:page][/:s]',
                    'constraints' => array(
                        'page'  => '[0-9]+',
                        's'  => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Subsidiary',
                        'action' => 'search',
                    )
                )
            ),
            'application_fees_list' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:appId/fees',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Fees',
                        'action' => 'feesList',
                    )
                )
            ),
             'application_ajax_get_payment_form' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:appId/ajax-get-payment-form',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Fees',
                        'action' => 'ajaxGetPaymentForm',
                    )
                )
            ),
            'application_ajax_get_payment' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:appId/ajax-get-payment',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Fees',
                        'action' => 'ajaxGetPayment',
                    )
                )
            ),
            'application_fees_payment' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:appId/:invoices/complete',
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Fees',
                        'action' => 'complete',
                    )
                )
            ),
            'application_licence_details' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:appId/licence-details',
                    'constraints' => array(
                        'appId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\Licence',
                        'action' => 'details',
                    )
                )
            ),
            'application_details_update' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:appId/details',
                    'constraints' => array(
                        'appId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Olcs\Controller\Application\EntityType',
                        'action' => 'details',
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Olcs\Controller\Index' => 'Olcs\Controller\IndexController',
            'Olcs\Controller\Lookup' => 'Olcs\Controller\LookupController',
            'Olcs\Controller\Application\New' => 'Olcs\Controller\Application\NewController',
            'Olcs\Controller\Application\Search' => 'Olcs\Controller\Application\SearchController',
            'Olcs\Controller\Application\Person' => 'Olcs\Controller\Application\PersonController',
            'Olcs\Controller\Application\Licence' => 'Olcs\Controller\Application\LicenceController',
            'Olcs\Controller\Application\EntityType' => 'Olcs\Controller\Application\EntityTypeController',
            'Olcs\Controller\Application\Subsidiary' => 'Olcs\Controller\Application\SubsidiaryController',
            'Olcs\Controller\VCase\New' => 'Olcs\Controller\VCase\NewController',
            'Olcs\Controller\VCase\List' => 'Olcs\Controller\VCase\ListController',
            'Olcs\Controller\VCase\Dashboard' => 'Olcs\Controller\VCase\DashboardController',
            'Olcs\Controller\VCase\Submission' => 'Olcs\Controller\VCase\SubmissionController',
            'Olcs\Controller\VCase\CaseNotes' => 'Olcs\Controller\VCase\CaseNotesController',
            'Olcs\Controller\VCase\Convictions' => 'Olcs\Controller\VCase\ConvictionsController',
            'Olcs\Controller\Application\Fees' => 'Olcs\Controller\Application\FeesController',
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
                $writer = new Zend\Log\Writer\Stream('/var/log/olcs/olcsLogfile.log');
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
            'olcs/index/index' => __DIR__ . '/../view/olcs/index/index.phtml',
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
            'DataListPlugin' => 'Olcs\Controller\Plugin\DataListPlugin',
            'transactional' => 'Olcs\Controller\Plugin\DoctrineTransaction',
        )
    ),
    'resource_strings' => __DIR__ . '/application.ini',
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'olcs_client_details' => array(
        'client_id' => 'OLCS',
        'client_secret' => 'olcssecret'
    )
);
