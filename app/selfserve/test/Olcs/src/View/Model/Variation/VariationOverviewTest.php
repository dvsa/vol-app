<?php

declare(strict_types=1);

/**
 * Variation Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\View\Model\Variation;

use Olcs\View\Model\Variation\VariationOverview;

/**
 * Variation Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class VariationOverviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test constructor with set variables
     */
    #[\PHPUnit\Framework\Attributes\Group('variationOverview')]
    public function testSetVariables(): void
    {
        $data = [
            'id' => 1,
            'createdOn' => '2014-01-01',
            'status' => ['id' => 'status'],
            'submissionForm' => 'form',
            'receivedDate' => '2014-01-01',
            'targetCompletionDate' => '2014-01-01'
        ];
        $overview = new VariationOverview($data);
        $this->assertEquals(1, $overview->applicationId);
        $this->assertEquals('01 January 2014', $overview->createdOn);
        $this->assertEquals('status', $overview->status);
        $this->assertEquals('2014-01-01', $overview->receivedDate);
        $this->assertEquals('2014-01-01', $overview->completionDate);
    }
}
