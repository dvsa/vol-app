<?php

declare(strict_types=1);

/**
 * Create ConditionUndertaking Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\ConditionUndertaking;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ConditionUndertaking\CreateConditionUndertaking;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\OperatingCentre as OperatingCentreRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\CreateConditionUndertaking as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;

/**
 * Create ConditionUndertaking Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateConditionUndertakingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateConditionUndertaking();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('OperatingCentre', OperatingCentreRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'cat_oc',
            'cat_lic',
            'cdt_und',
            'cdt_con'
        ];

        $this->references = [
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ],
            OperatingCentreEntity::class => [
                16 => m::mock(OperatingCentreEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandAttachedToOc(): void
    {
        $command = Cmd::create(
            [
            'licence' => 7,
            'application' => 8,
            'case' => 24,
            'conditionType' => 'cdt_und',
            'notes' => 'Notes',
            'isFulfilled' => 'N',
            'attachedTo' => 'cat_oc',
            'operatingCentre' => 16,
            'action' => 'X',
            ]
        );

        /** @var OperatingCentreEntity $operatingCentreEntity */
        $operatingCentreEntity = m::mock(OperatingCentreEntity::class)->makePartial();
        $operatingCentreEntity->setId(16);

        /** @var LicenceEntity $licenceEntity */
        $licenceEntity = m::mock(LicenceEntity::class)->makePartial();
        $licenceEntity->setId(7);

        /** @var ApplicationEntity $applicationEntity */
        $applicationEntity = m::mock(ApplicationEntity::class)->makePartial();
        $applicationEntity->setId(8);

        /** @var CasesEntity $caseEntity */
        $caseEntity = m::mock(CasesEntity::class)->makePartial();
        $caseEntity->setLicence($licenceEntity);

        $this->repoMap['OperatingCentre']
            ->shouldReceive('fetchById')
            ->with(16)
            ->andReturn($operatingCentreEntity);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with(7)
            ->andReturn($licenceEntity);

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with(8)
            ->andReturn($applicationEntity);

        $this->repoMap['Cases']
            ->shouldReceive('fetchById')
            ->with(24)
            ->andReturn($caseEntity);

        /** @var ConditionUndertakingEntity $entity */
        $conditionUndertakingEntity = null;

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('save')
            ->with(m::type(ConditionUndertakingEntity::class))
            ->andReturnUsing(
                function (ConditionUndertakingEntity $conditionUndertaking) use (&$conditionUndertakingEntity) {
                    $conditionUndertakingEntity = $conditionUndertaking;
                    $conditionUndertaking->setId(99);
                    $this->assertSame('X', $conditionUndertaking->getAction());
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('ConditionUndertaking created', $result->getMessages());
    }

    public function testHandleCommandAttachedToLicence(): void
    {
        $command = Cmd::create(
            [
                'licence' => 7,
                'application' => 8,
                'case' => 24,
                'conditionType' => 'cdt_con',
                'notes' => 'Notes',
                'isFulfilled' => 'N',
                'attachedTo' => 'cat_lic'
            ]
        );

        /** @var OperatingCentreEntity $operatingCentreEntity */
        $operatingCentreEntity = m::mock(OperatingCentreEntity::class)->makePartial();
        $operatingCentreEntity->setId(16);

        /** @var LicenceEntity $licenceEntity */
        $licenceEntity = m::mock(LicenceEntity::class)->makePartial();
        $licenceEntity->setId(7);

        /** @var ApplicationEntity $applicationEntity */
        $applicationEntity = m::mock(ApplicationEntity::class)->makePartial();
        $applicationEntity->setId(8);

        $licenceEntity = m::mock(ApplicationEntity::class)->makePartial();
        $licenceEntity->setId(8);

        /** @var CasesEntity $caseEntity */
        $caseEntity = m::mock(CasesEntity::class)->makePartial();
        $caseEntity->setLicence($licenceEntity);

        $this->repoMap['OperatingCentre']
            ->shouldReceive('fetchById')
            ->with(16)
            ->andReturn($operatingCentreEntity);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with(7)
            ->andReturn($licenceEntity);

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with(8)
            ->andReturn($applicationEntity);

        $this->repoMap['Cases']
            ->shouldReceive('fetchById')
            ->with(24)
            ->andReturn($caseEntity);

        /** @var ConditionUndertakingEntity $entity */
        $conditionUndertakingEntity = null;

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('save')
            ->with(m::type(ConditionUndertakingEntity::class))
            ->andReturnUsing(
                function (ConditionUndertakingEntity $conditionUndertaking) use (&$conditionUndertakingEntity) {
                    $conditionUndertakingEntity = $conditionUndertaking;
                    $conditionUndertaking->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('ConditionUndertaking created', $result->getMessages());
    }
}
