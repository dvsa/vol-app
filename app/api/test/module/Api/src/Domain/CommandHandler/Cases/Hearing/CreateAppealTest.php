<?php

declare(strict_types=1);

/**
 * Create Appeal Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as AppealEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing\CreateAppeal;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Appeal as AppealRepo;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateAppeal as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Appeal Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateAppealTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateAppeal();
        $this->mockRepo('Appeal', AppealRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'appeal_r_lic_non_pi',
            'appeal_o_dis'
        ];

        $this->references = [
            Cases::class => [
                24 => m::mock(Cases::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $this->references[Cases::class][24]
            ->shouldReceive('hasAppeal')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $data = [
            'appealNo' => '1231234',
            'case' => 24,
            'deadlineDate' => '2015-05-05',
            'appealDate' => '2015-01-05',
            'reason' => 'appeal_r_lic_non_pi',
            'outlineGround' => 'asdfasdf',
            'hearingDate' => '2015-05-05',
            'decisionDate' => '2015-05-05',
            'papersDueDate' => '2015-05-05',
            'papersDueTcDate' => '2015-05-06',
            'papersSentDate' => '2015-05-05',
            'papersSentTcDate' => '2015-07-07',
            'comment' => 'booo',
            'outcome' => 'appeal_o_dis',
            'isWithdrawn' => 'Y',
            'withdrawnDate' => '2015-05-05',
            'dvsaNotified' => 'Y',
        ];

        $command = Cmd::create($data);

        /** @var AppealEntity $savedAppeal */
        $savedAppeal = null;

        $this->repoMap['Appeal']
            ->shouldReceive('save')
            ->with(m::type(AppealEntity::class))
            ->andReturnUsing(
                function (AppealEntity $appeal) use (&$savedAppeal) {
                    $appeal->setId(99);
                    $savedAppeal = $appeal;
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'appeal' => 99,
            ],
            'messages' => [
                'Appeal created'
            ]
        ];
        $this->assertEquals($expectedResult, $result->toArray());

        $this->assertEquals(99, $savedAppeal->getId());
        $this->assertEquals($data['appealNo'], $savedAppeal->getAppealNo());
        $this->assertSame($this->references[Cases::class][24], $savedAppeal->getCase());
        $this->assertEquals($data['deadlineDate'], $savedAppeal->getDeadlineDate()->format('Y-m-d'));
        $this->assertEquals($data['appealDate'], $savedAppeal->getAppealDate()->format('Y-m-d'));
        $this->assertSame($this->refData['appeal_r_lic_non_pi'], $savedAppeal->getReason());
        $this->assertEquals($data['outlineGround'], $savedAppeal->getOutlineGround());
        $this->assertEquals($data['hearingDate'], $savedAppeal->getHearingDate()->format('Y-m-d'));
        $this->assertEquals($data['decisionDate'], $savedAppeal->getDecisionDate()->format('Y-m-d'));
        $this->assertEquals($data['papersDueDate'], $savedAppeal->getPapersDueDate()->format('Y-m-d'));
        $this->assertEquals($data['papersDueTcDate'], $savedAppeal->getPapersDueTcDate()->format('Y-m-d'));
        $this->assertEquals($data['papersSentDate'], $savedAppeal->getPapersSentDate()->format('Y-m-d'));
        $this->assertEquals($data['papersSentTcDate'], $savedAppeal->getPapersSentTcDate()->format('Y-m-d'));
        $this->assertEquals($data['comment'], $savedAppeal->getComment());
        $this->assertSame($this->refData['appeal_o_dis'], $savedAppeal->getOutcome());
        $this->assertEquals($data['withdrawnDate'], $savedAppeal->getWithdrawnDate()->format('Y-m-d'));
        $this->assertEquals($data['dvsaNotified'], $savedAppeal->getDvsaNotified());
    }

    public function testHandleCommandWithoutOptional(): void
    {
        $this->references[Cases::class][24]
            ->shouldReceive('hasAppeal')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $data = [
            'appealNo' => '1231234',
            'case' => 24,
            'appealDate' => '2015-01-05',
            'reason' => 'appeal_r_lic_non_pi',
            'isWithdrawn' => 'N',
            'dvsaNotified' => 'Y',
        ];

        $command = Cmd::create($data);

        /** @var AppealEntity $savedAppeal */
        $savedAppeal = null;

        $this->repoMap['Appeal']
            ->shouldReceive('save')
            ->with(m::type(AppealEntity::class))
            ->andReturnUsing(
                function (AppealEntity $appeal) use (&$savedAppeal) {
                    $appeal->setId(99);
                    $savedAppeal = $appeal;
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'appeal' => 99,
            ],
            'messages' => [
                'Appeal created'
            ]
        ];
        $this->assertEquals($expectedResult, $result->toArray());

        $this->assertEquals(99, $savedAppeal->getId());
        $this->assertEquals($data['appealNo'], $savedAppeal->getAppealNo());
        $this->assertSame($this->references[Cases::class][24], $savedAppeal->getCase());
        $this->assertNull($savedAppeal->getDeadlineDate());
        $this->assertEquals($data['appealDate'], $savedAppeal->getAppealDate()->format('Y-m-d'));
        $this->assertSame($this->refData['appeal_r_lic_non_pi'], $savedAppeal->getReason());
        $this->assertNull($savedAppeal->getOutlineGround());
        $this->assertNull($savedAppeal->getHearingDate());
        $this->assertNull($savedAppeal->getDecisionDate());
        $this->assertNull($savedAppeal->getPapersDueDate());
        $this->assertNull($savedAppeal->getPapersDueTcDate());
        $this->assertNull($savedAppeal->getPapersSentDate());
        $this->assertNull($savedAppeal->getPapersSentTcDate());
        $this->assertNull($savedAppeal->getComment());
        $this->assertNull($savedAppeal->getOutcome());
        $this->assertNull($savedAppeal->getWithdrawnDate());
        $this->assertEquals($data['dvsaNotified'], $savedAppeal->getDvsaNotified());
    }

    public function testHandleCommandExistingAppeal(): void
    {
        $this->references[Cases::class][24]
            ->shouldReceive('hasAppeal')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $data = [
            'appealNo' => '1231234',
            'case' => 24,
            'appealDate' => '2015-01-05',
            'reason' => 'appeal_r_lic_non_pi',
            'isWithdrawn' => 'N',
            'dvsaNotified' => 'Y',
        ];

        $command = Cmd::create($data);

        $this->repoMap['Appeal']
            ->shouldReceive('save')
            ->never();

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
