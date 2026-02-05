<?php

declare(strict_types=1);

/**
 * ProcessToValidTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToValid as Command;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule\ProcessToValid as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule as LicenceStatusRule;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * ProcessToValidTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ProcessToValidTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LicenceStatusRule', Repo::class);
        $this->mockRepo('LicenceVehicle', Repo::class);

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
            [Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT],
            [Licence::LICENCE_STATUS_GRANTED],
            [Licence::LICENCE_STATUS_NOT_SUBMITTED],
            [Licence::LICENCE_STATUS_NOT_TAKEN_UP],
            [Licence::LICENCE_STATUS_REFUSED],
            [Licence::LICENCE_STATUS_REVOKED],
            [Licence::LICENCE_STATUS_SURRENDERED],
            [Licence::LICENCE_STATUS_TERMINATED],
            [Licence::LICENCE_STATUS_UNDER_CONSIDERATION],
            [Licence::LICENCE_STATUS_VALID],
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

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchToValid')->once()->andReturn($licenceStatueRules);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            ["To Valid Licence Status Rule ID 556 licence is not curtailed or suspended"],
            $result->getMessages()
        );
    }

    public static function dataProviderHandleCommand(): array
    {
        return [
            [Licence::LICENCE_STATUS_CURTAILED],
            [Licence::LICENCE_STATUS_SUSPENDED],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderHandleCommand')]
    public function testHandleCommand(mixed $status): void
    {
        $command = Command::create([]);

        $licenceStatueRules = [
            $this->createLicenceStatusRule($status),
        ];

        $this->repoMap['LicenceStatusRule']->shouldReceive('fetchToValid')->once()->andReturn($licenceStatueRules);
        $this->repoMap['LicenceStatusRule']->shouldReceive('save')->with(m::type(LicenceStatusRule::class))->once();
        $this->repoMap['LicenceVehicle']->shouldReceive('clearVehicleSection26')->with(46)->once()->andReturn(23);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            $this->refData[Licence::LICENCE_STATUS_VALID],
            $licenceStatueRules[0]->getLicence()->getStatus()
        );
        $this->assertNull($licenceStatueRules[0]->getLicence()->getRevokedDate());
        $this->assertNull($licenceStatueRules[0]->getLicence()->getCurtailedDate());
        $this->assertNull($licenceStatueRules[0]->getLicence()->getSuspendedDate());

        $this->assertInstanceOf(\DateTime::class, $licenceStatueRules[0]->getEndProcessedDate());
        $this->assertSame(
            (new \DateTime())->format('Y-m-d'),
            $licenceStatueRules[0]->getEndProcessedDate()->format('Y-m-d')
        );

        $this->assertSame(
            ["To Valid Licence Status Rule ID 556 success"],
            $result->getMessages()
        );
    }

    /**
     * @param string $licenceStatus
     *
     * @return LicenceStatusRule
     */
    protected function createLicenceStatusRule(mixed $licenceStatus): mixed
    {
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setRevokedDate('AAA');
        $licence->setCurtailedDate('BBB');
        $licence->setSuspendedDate('CCC');
        $licence->setId((46));
        $licence->setStatus($this->refData[$licenceStatus]);

        $lsr = new LicenceStatusRule($licence, $this->refData[$licenceStatus]);
        $lsr->setId(556);

        $licencedVehicle1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle(
            $licence,
            (new \Dvsa\Olcs\Api\Entity\Vehicle\Vehicle())->setSection26(9)
        );
        $licence->getLicenceVehicles()->add($licencedVehicle1);

        $licencedVehicle2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle(
            $licence,
            (new \Dvsa\Olcs\Api\Entity\Vehicle\Vehicle())->setSection26(9)
        );
        $licence->getLicenceVehicles()->add($licencedVehicle2);

        return $lsr;
    }
}
