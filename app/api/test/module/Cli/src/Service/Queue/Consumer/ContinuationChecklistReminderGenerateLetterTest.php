<?php

declare(strict_types=1);

/**
 * Continuation Checklist Reminder Generate Letter Queue Consumer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklistReminderGenerateLetter as Sut;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Mockery as m;

/**
 * Continuation Checklist Reminder Generate Letter Queue Consumer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContinuationChecklistReminderGenerateLetterTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $mockUser = m::mock(UserEntity::class)->shouldReceive('getId')->andReturn(2)->getMock();
        $item = new QueueEntity();
        $item->setEntityId(69);
        $item->setCreatedBy($mockUser);

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(
            ['id' => 69, 'user' => 2],
            $result
        );
    }
}
