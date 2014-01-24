<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace OlcsSelfserve;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Config\Reader\Ini;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    //__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'OlcsSelfserve' => __DIR__ . '/src/OlcsSelfserve',
                ),
            ),
        );
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'olcsFormDateSelect' => '\OlcsSelfserve\View\Helper\FormDateSelect',
                'formatAttributes' => '\OlcsSelfserve\View\Helper\FormatAttributes',
                'simpleDateFormat' => '\OlcsSelfserve\View\Helper\SimpleDateFormat',
                'tooltipHelper' => '\OlcsSelfserve\View\Helper\TooltipHelper',
            ),
            'factories' => array(
              'resourceHelper'=> function($sm) {
                    $config = $sm->getServiceLocator()->get('Config');
                    if (empty($config['resource_strings'])) {
                        $resources = array();
                    } else {
                        $reader = new Ini();
                        $data = $reader->fromFile($config['resource_strings']);
                        $resources = $data['section'];
                    }
                    $helper  = new \OlcsSelfserve\View\Helper\ResourceHelper($resources);
                    return $helper;
              }
            )
        );   
   }
}
