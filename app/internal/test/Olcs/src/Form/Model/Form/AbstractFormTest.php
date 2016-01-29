<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormTest as BaseAbstract;
use OlcsTest\Bootstrap;

/**
 * Class AbstractFormTest
 * @package OlcsTest\Form\Model\Form
 */
abstract class AbstractFormTest extends BaseAbstract
{
    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        return Bootstrap::getRealServiceManager();
    }
}
