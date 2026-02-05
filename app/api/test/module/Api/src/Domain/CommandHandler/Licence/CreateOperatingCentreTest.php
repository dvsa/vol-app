<?php

declare(strict_types=1);

/**
 * Create Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateOperatingCentre as CommandHandler;
use Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Licence\CreateOperatingCentre as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Create Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateOperatingCentreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('OperatingCentre', Repository\OperatingCentre::class);
        $this->mockRepo('LicenceOperatingCentre', Repository\LicenceOperatingCentre::class);

        $this->mockedSmServices['OperatingCentreHelper'] = m::mock(OperatingCentreHelper::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockedSmServices[CacheEncryption::class] = m::mock(CacheEncryption::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommandWithoutPermission(): void
    {
        $data = [];
        $command = Cmd::create($data);

        $this->expectException(ForbiddenException::class);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => 111
        ];
        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->initCollections();

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licence);

        $oc = m::mock(OperatingCentre::class)->makePartial();

        $this->mockedSmServices['OperatingCentreHelper']->shouldReceive('validate')
            ->once()
            ->with($licence, $command, false)
            ->shouldReceive('createOperatingCentre')
            ->once()
            ->with($command, $this->commandHandler, m::type(Result::class), $this->repoMap['OperatingCentre'])
            ->andReturn($oc)
            ->shouldReceive('saveDocuments')
            ->with($licence, $oc, $this->repoMap['Document'])
            ->shouldReceive('updateOperatingCentreLink')
            ->once()
            ->with(
                m::type(LicenceOperatingCentre::class),
                $licence,
                $command,
                $this->repoMap['LicenceOperatingCentre']
            );

        $this->expectedLicenceCacheClear($licence);
        $this->sut->handleCommand($command);

        $this->assertEquals(1, $licence->getOperatingCentres()->count());

        /** @var LicenceOperatingCentre $savedLoc */
        $savedLoc = $licence->getOperatingCentres()->first();
        $savedOc = $savedLoc->getOperatingCentre();

        $this->assertSame($oc, $savedOc);
    }
}
