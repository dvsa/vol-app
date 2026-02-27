<?php

declare(strict_types=1);

/**
 * ProcessToRevokeCurtailSuspendTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToRevokeCurtailSuspend as Command;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule\ProcessToRevokeCurtailSuspend as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule as LicenceStatusRule;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * ProcessToRevokeCurtailSuspendTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ProcessToRevokeCurtailSuspendTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LicenceStatusRule', Repo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
            Licence::LICENCE_STATUS_CURTAILED,
            Licence::LICENCE_STATUS_GRANTED,
            Licence::LICENCE_STATUS_NOT_SUBMITTED,
            Licence::LICENCE_STATUS_NOT_TAKEN_UP,
            Licence::LICENCE_STATUS_REFUSED,
            Licence::LICENCE_STATUS_REVOKED,
            Licence::LICENCE_STATUS_SURRENDERED,
            Licence::LICENCE_STATUS_SUSPENDED,
            Licence::LICENCE_STATUS_TERMINATED,
            Licence::LICENCE_STATUS_UNDER_CONSIDERATION,
            Licence::LICENCE_STATUS_VALID,
            Licence::LICENCE_STATUS_WITHDRAWN,
        ];

        $this->references = [];

        parent::initReferences();
    }

    public static function dataProviderHandleCommandOtherStatus(): array
    {
        return [
            [Licence::LICENCE_STATUS_CURTAILED],
            [Licence::LICENCE_STATUS_SUSPENDED],
            [Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT],
            [Licence::LICENCE_STATUS_GRANTED],
            [Licence::LICENCE_STATUS_NOT_SUBMITTED],
            [Licence::LICENCE_STATUS_NOT_TAKEN_UP],
            [Licence::LICENCE_STATUS_REFUSED],
            [Licence::LICENCE_STATUS_REVOKED],
            [Licence::LICENCE_STATUS_SURRENDERED],
            [Licence::LICENCE_STATUS_TERMINATED],
            [Licence::LICENCE_STATUS_UNDER_CONSIDERATION],
            [Licence::LICENCE_STATUS_WITHDRAWN],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderHandleCommandOtherStatus')]
    public function testHandleCommandOtherStatus(mixed $status): void
    {
        $command = Command::create([]);

        $licenceStatueRules = [
            $this->createLicenceStatusRule($status),
        ];

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchRevokeCurtailSuspend')->once()
            ->andReturn($licenceStatueRules);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            ["To Revoked, Curtailed, Suspended Licence Status Rule ID 556 licence is not valid"],
            $result->getMessages()
        );
    }

    public function testHandleCommandSuspended(): void
    {
        $command = Command::create([]);

        $licenceStatueRules = [
            $this->createLicenceStatusRule(Licence::LICENCE_STATUS_VALID, Licence::LICENCE_STATUS_SUSPENDED),
        ];

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchRevokeCurtailSuspend')->once()
            ->andReturn($licenceStatueRules);
        $this->repoMap['LicenceStatusRule']->shouldReceive('save')->with(m::type(LicenceStatusRule::class))->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\SuspendLicence::class,
            ['id' => 46, 'deleteLicenceStatusRules' => false],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\DateTime::class, $licenceStatueRules[0]->getStartProcessedDate());
        $this->assertSame(
            (new \DateTime())->format('Y-m-d'),
            $licenceStatueRules[0]->getStartProcessedDate()->format('Y-m-d')
        );

        $this->assertSame(
            ["To Revoked, Curtailed, Suspended Licence Status Rule ID 556 success"],
            $result->getMessages()
        );
    }

    public function testHandleCommandRevoked(): void
    {
        $command = Command::create([]);

        $licenceStatueRules = [
            $this->createLicenceStatusRule(Licence::LICENCE_STATUS_VALID, Licence::LICENCE_STATUS_REVOKED),
        ];

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchRevokeCurtailSuspend')->once()
            ->andReturn($licenceStatueRules);
        $this->repoMap['LicenceStatusRule']->shouldReceive('save')->with(m::type(LicenceStatusRule::class))->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\RevokeLicence::class,
            ['id' => 46, 'deleteLicenceStatusRules' => false],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\DateTime::class, $licenceStatueRules[0]->getStartProcessedDate());
        $this->assertSame(
            (new \DateTime())->format('Y-m-d'),
            $licenceStatueRules[0]->getStartProcessedDate()->format('Y-m-d')
        );

        $this->assertSame(
            ["To Revoked, Curtailed, Suspended Licence Status Rule ID 556 success"],
            $result->getMessages()
        );
    }

    public function testHandleCommandCurtailed(): void
    {
        $command = Command::create([]);

        $licenceStatueRules = [
            $this->createLicenceStatusRule(Licence::LICENCE_STATUS_VALID, Licence::LICENCE_STATUS_CURTAILED),
        ];

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchRevokeCurtailSuspend')->once()
            ->andReturn($licenceStatueRules);
        $this->repoMap['LicenceStatusRule']->shouldReceive('save')->with(m::type(LicenceStatusRule::class))->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\CurtailLicence::class,
            ['id' => 46, 'deleteLicenceStatusRules' => false],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\DateTime::class, $licenceStatueRules[0]->getStartProcessedDate());
        $this->assertSame(
            (new \DateTime())->format('Y-m-d'),
            $licenceStatueRules[0]->getStartProcessedDate()->format('Y-m-d')
        );

        $this->assertSame(
            ["To Revoked, Curtailed, Suspended Licence Status Rule ID 556 success"],
            $result->getMessages()
        );
    }

    public static function dataProviderHandleCommandInvalidToLicence(): array
    {
        return [
            [Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT],
            [Licence::LICENCE_STATUS_GRANTED],
            [Licence::LICENCE_STATUS_NOT_SUBMITTED],
            [Licence::LICENCE_STATUS_NOT_TAKEN_UP],
            [Licence::LICENCE_STATUS_REFUSED],
            [Licence::LICENCE_STATUS_SURRENDERED],
            [Licence::LICENCE_STATUS_TERMINATED],
            [Licence::LICENCE_STATUS_UNDER_CONSIDERATION],
            [Licence::LICENCE_STATUS_VALID],
            [Licence::LICENCE_STATUS_WITHDRAWN],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderHandleCommandInvalidToLicence')]
    public function testHandleCommandInvalidToLicence(mixed $status): void
    {
        $command = Command::create([]);

        $licenceStatueRules = [
            $this->createLicenceStatusRule(Licence::LICENCE_STATUS_VALID, $status),
        ];

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchRevokeCurtailSuspend')->once()
            ->andReturn($licenceStatueRules);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $this->sut->handleCommand($command);
    }

    /**
     * @param string $licenceStatus
     *
     * @return LicenceStatusRule
     */
    protected function createLicenceStatusRule(mixed $licenceStatus, mixed $ruleStatus = Licence::LICENCE_STATUS_VALID): mixed
    {
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId((46));
        $licence->setStatus($this->refData[$licenceStatus]);

        $lsr = new LicenceStatusRule($licence, $this->refData[$ruleStatus]);
        $lsr->setId(556);

        return $lsr;
    }
}
