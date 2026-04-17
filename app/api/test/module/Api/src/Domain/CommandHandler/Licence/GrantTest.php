<?php

declare(strict_types=1);

/**
 * GrantTest.php
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\Grant as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Licence\Grant as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Class GrantTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class GrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['lsts_granted'];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['id' => 532]);

        $licence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId(532);

        $this->repoMap['Licence']->shouldReceive('fetchById')->with(532)->once()->andReturn($licence);
        $this->repoMap['Licence']->shouldReceive('save')->once()->andReturnUsing(
            function (LicenceEntity $saveLicence) {
                $this->assertSame($this->refData['lsts_granted'], $saveLicence->getStatus());
            }
        );

        $this->expectedLicenceCacheClearSideEffect(532);
        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Licence 532 has been granted"], $result->getMessages());
    }
}
