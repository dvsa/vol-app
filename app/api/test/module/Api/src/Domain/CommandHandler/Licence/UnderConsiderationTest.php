<?php

declare(strict_types=1);

/**
 * UnderConsiderationTest.php
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForLicence;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UnderConsideration as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Licence\UnderConsideration as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Class UnderConsiderationTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class UnderConsiderationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', Licence::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['lsts_consideration'];

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
                $this->assertSame($this->refData['lsts_consideration'], $saveLicence->getStatus());
            }
        );

        $cacheClearResult = new Result();
        $cacheClearResult->addMessage('cache clear msg');
        $this->expectedSideEffect(ClearForLicence::class, ['id' => 532], $cacheClearResult);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Licence 532 has been set to under consideration","cache clear msg"], $result->getMessages());
    }
}
