<?php

/**
 * Hours per week
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\HoursPerWeek;

/**
 * Hours per week
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HoursPerWeekTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test get messages
     *
     * @group hoursPerWeekType
     */
    public function testGetMessages(): void
    {
        $element = new HoursPerWeek();
        $element->setMessages(['messages']);
        $this->assertEquals(['messages'], $element->getMessages());
    }
}
