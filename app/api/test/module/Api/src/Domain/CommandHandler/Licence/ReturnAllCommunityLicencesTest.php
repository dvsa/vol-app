<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Licence\ReturnAllCommunityLicences as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\ReturnAllCommunityLicences as CommandHandler;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * ReturnAllCommunityLicencesTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ReturnAllCommunityLicencesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('CommunityLic', \Dvsa\Olcs\Api\Domain\Repository\CommunityLic::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLic::STATUS_RETURNDED => m::mock(RefData::class)->makePartial()->setDescription('returned'),
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 608,
        ];
        $command = Cmd::create($data);

        $licence = m::mock(Licence::class);
        $licence->expects('getId')->andReturn(608);
        $this->expectedLicenceCacheClear($licence);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(608)
            ->andReturn($licence);

        $this->repoMap['CommunityLic']->shouldReceive('expireAllForLicence')
            ->with(608, CommunityLic::STATUS_RETURNDED)
            ->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences::class,
            ['id' => 608],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('UpdateTotalCommunityLicences')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Community licence(s) updated to returned',
                'UpdateTotalCommunityLicences',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
