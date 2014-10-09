<?php

/**
 * Controller Test Abstract
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Controller Test Abstract
 */
abstract class ControllerTestAbstract extends AbstractHttpControllerTestCase
{
    protected $proxyMethdods = [];

    /**
     * Tests the methods that simply call other methdods by proxy.
     */
    public function testProxyMethodsAreCalled()
    {
        // if there are none, return - no test required.
        if (empty($this->proxyMethdods)) {
            $this->assertTrue(true);
            return;
        }

        $sut = $this->getMock($this->testClass, array_values($this->proxyMethdods));

        foreach ($this->proxyMethdods as $methodName => $proxy) {
            $sut->expects($this->once())->method($proxy)->will($this->returnValue($proxy));
            $this->assertEquals($proxy, $sut->{$methodName}());
        }
    }
}
