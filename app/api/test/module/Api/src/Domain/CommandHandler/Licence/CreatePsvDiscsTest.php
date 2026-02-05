<?php

declare(strict_types=1);

/**
 * Create Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreatePsvDiscs;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs as Cmd;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc as PsvDiscEntity;

/**
 * Create Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreatePsvDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePsvDiscs();
        $this->mockRepo('PsvDisc', PsvDisc::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [

        ];

        $this->references = [
            LicenceEntity::class => [
                111 => m::mock(LicenceEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandFailedValidation(): void
    {
        $data = [
            'licence' => 111,
            'amount' => 6
        ];

        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = $this->references[LicenceEntity::class][111];
        $licence->updateTotAuthHgvVehicles(10);

        $licence->shouldReceive('getPsvDiscs->matching->count')
            ->andReturn(5);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => 111,
            'amount' => 2
        ];

        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = $this->references[LicenceEntity::class][111];
        $licence->updateTotAuthHgvVehicles(10);

        $licence->shouldReceive('getPsvDiscs->matching->count')
            ->andReturn(5);

        $this->repoMap['PsvDisc']->shouldReceive('createPsvDiscs')->with(111, 2, false);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 PSV Disc(s) created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
