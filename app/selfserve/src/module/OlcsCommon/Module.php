<?php
/**
 * OLCS Common Module
 *
 * Contains code shared between all OLCS modules
 *
 * @package     olcscommon
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
