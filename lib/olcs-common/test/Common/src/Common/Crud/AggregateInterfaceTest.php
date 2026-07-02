<?php

/**
 * Class AggregateInterfaceTest
 *
 * @package CommonTest\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace CommonTest\Crud;

/**
 * Class AggregateInterfaceTest
 *
 * @package CommonTest\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class AggregateInterfaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the aggregate interface and in turn also tests the individual interfaces.
     */
    public function testAggregateInterfaceMethodsExist(): void
    {
        $interface = $this->createMock(\Common\Crud\AggregateInterface::class);

        $this->assertTrue(method_exists($interface, 'create'));
        $this->assertTrue(method_exists($interface, 'get'));
        $this->assertTrue(method_exists($interface, 'getList'));
        $this->assertTrue(method_exists($interface, 'update'));
        $this->assertTrue(method_exists($interface, 'delete'));
    }
}
