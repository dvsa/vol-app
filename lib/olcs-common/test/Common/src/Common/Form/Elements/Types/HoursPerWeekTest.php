<?php

/**
 * Hours per week
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\HoursPerWeek;

/**
 * Hours per week
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class HoursPerWeekTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test get messages
     */
    #[\PHPUnit\Framework\Attributes\Group('hoursPerWeekType')]
    public function testGetMessages(): void
    {
        $element = new HoursPerWeek();
        $element->setMessages(['messages']);
        $this->assertSame(['messages'], $element->getMessages());
    }
}
