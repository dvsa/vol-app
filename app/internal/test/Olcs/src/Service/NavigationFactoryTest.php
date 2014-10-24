<?php

namespace OlcsTest\Service;

use Olcs\Service\NavigationFactory;
use Mockery as m;

use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Class NavigationFactoryTest
 * @package OlcsTest\Service
 */
class NavigationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $config = array(
            'modules'                 => array(),
            'module_listener_options' => array(
                'config_cache_enabled' => false,
                'cache_dir'            => 'data/cache',
                'module_paths'         => array(),
                'extra_config'         => array(
                    'service_manager' => array(
                        'factories' => array(
                            'Config' => function () {
                                return array(
                                    'navigation' => array(
                                        'file'    => __DIR__ . '/_files/navigation.xml',
                                        'default' => array(
                                            array(
                                                'label' => 'Page 1',
                                                'uri'   => 'page1.html'
                                            ),
                                            array(
                                                'label' => 'MVC Page',
                                                'route' => 'foo',
                                                'pages' => array(
                                                    array(
                                                        'label' => 'Sub MVC Page',
                                                        'route' => 'foo'
                                                    )
                                                )
                                            ),
                                            array(
                                                'label' => 'Page 3',
                                                'uri'   => 'page3.html'
                                            )
                                        )
                                    )
                                );
                            }
                        )
                    ),
                )
            ),
        );

        $sm = $this->serviceManager = new ServiceManager(new ServiceManagerConfig);
        $sm->setService('ApplicationConfig', $config);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $app = $this->serviceManager->get('Application');
        $app->getMvcEvent()->setRouteMatch(
            new RouteMatch(
                array(
                    'controller' => 'post',
                    'action'     => 'view',
                    'id'         => '1337',
                )
            )
        );
    }

    public function testCreateService()
    {
        $sut = new NavigationFactory();
        $sut->setServiceLocator($this->serviceManager);

        $this->assertInstanceOf('Zend\Navigation\Navigation', $sut->getNavigation([]));
    }
}
