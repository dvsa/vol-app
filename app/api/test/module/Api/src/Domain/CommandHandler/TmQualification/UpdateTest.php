<?php

declare(strict_types=1);

/**
 * TmQualification / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmQualification;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TmQualification\Update as TmQualificationUpdate;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TmQualification\Update as Cmd;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;

/**
 * TmQualification / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TmQualificationUpdate();
        $this->mockRepo('TmQualification', TmQualificationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'qtype'
        ];

        $this->references = [
            CountryEntity::class => [
                'GB' => m::mock(CountryEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 1;
        $data = [
            'id' => $id,
            'version' => 2,
            'qualificationType' => 'qtype',
            'serialNo' => '123',
            'issuedDate' => '2015-01-01',
            'countryCode' => 'GB',
        ];

        $command = Cmd::create($data);

        $mockTmQualification = m::mock(TmQualificationEntity::class)
            ->shouldReceive('updateTmQualification')
            ->with(
                $this->refData['qtype'],
                '123',
                '2015-01-01',
                $this->references[CountryEntity::class]['GB']
            )
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id)
            ->once()
            ->getMock();

        $this->repoMap['TmQualification']
            ->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($mockTmQualification)
            ->once()
            ->shouldReceive('save')
            ->with($mockTmQualification)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);
        $this->assertEquals(
            $result->toArray(),
            [
                'id' => [
                    'tmQualification' => 1
                ],
                'messages' => [
                    'TmQualification updated successfully'
                ]
            ]
        );
    }
}
