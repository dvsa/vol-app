<?php

namespace OlcsTest\FormTest;

use Common\FormTester\AbstractFormTest as BaseAbstract;
use OlcsTest\Bootstrap;

abstract class AbstractFormTest extends BaseAbstract
{
    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }
}