<?php

/**
 * ApplicationTransportManagers Element Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\ApplicationTransportManagers;

/**
 * ApplicationTransportManagers test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ApplicationTransportManagersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the element configuration
     */
    public function testElement(): void
    {
        $element = new ApplicationTransportManagers();

        $this->assertTrue($element->has('application'));
        $this->assertTrue($element->has('search'));
    }


    /**
     * Test get and set messages
     *
     */
    public function testGetMessages(): void
    {
        $element = new ApplicationTransportManagers();
        $element->setMessages(['messages']);
        $this->assertEquals(['messages'], $element->getMessages());
    }
}
