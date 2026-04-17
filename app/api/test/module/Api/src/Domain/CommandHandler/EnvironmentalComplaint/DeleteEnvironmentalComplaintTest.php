<?php

declare(strict_types=1);

/**
 * Delete Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\EnvironmentalComplaint;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\EnvironmentalComplaint\DeleteEnvironmentalComplaint;
use Dvsa\Olcs\Api\Domain\Repository\Complaint;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\DeleteEnvironmentalComplaint as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Delete Environmental Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class DeleteEnvironmentalComplaintTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteEnvironmentalComplaint();
        $this->mockRepo('Complaint', Complaint::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'ct_complainant'
        ];

        $this->references = [
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'id' => 99,
                'version' => 1
            ]
        );

        /** @var PersonEntity $person */
        $person = m::mock(PersonEntity::class)->makePartial();
        $person->setId(44);

        /** @var ContactDetailsEntity $complainantContactDetails */
        $complainantContactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $complainantContactDetails->setId(33);
        $complainantContactDetails->setPerson($person);

        /** @var ComplaintEntity $complaint */
        $complaint = m::mock(ComplaintEntity::class)->makePartial();
        $complaint->setId($command->getId());
        $complaint->setComplainantContactDetails($complainantContactDetails);

        $this->repoMap['Complaint']->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($complaint)
            ->once()
            ->shouldReceive('delete')
            ->with(m::type(ComplaintEntity::class))
            ->andReturnUsing(
                function (ComplaintEntity $complaint) {
                    $complaint->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 99 deleted', $result->getMessages());
    }
}
