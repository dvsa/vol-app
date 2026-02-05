<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\UpdateSi as Sut;
use Dvsa\Olcs\Api\Domain\Repository\SeriousInfringement as SiRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\UpdateSi as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateSi Test
 */
class UpdateSiTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('SeriousInfringement', SiRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            CountryEntity::class => [
                'GB' => m::mock(CountryEntity::class)
            ],
            SiCategoryEntity::class => [
                SiCategoryEntity::ERRU_DEFAULT_CATEGORY => m::mock(SiCategoryEntity::class)
            ],
            SiCategoryTypeEntity::class => [
                100 => m::mock(SiCategoryTypeEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 123;
        $version = 1;
        $notificationNumber = 'ABC123';
        $siCategoryType = 100;
        $infringementDate = '2015-12-25';
        $checkDate = '2015-12-26';
        $memberStateCode = 'GB';
        $reason = 'reason';

        $command = Cmd::Create(
            [
                'id' => $id,
                'version' => $version,
                'notificationNumber' => $notificationNumber,
                'siCategoryType' => $siCategoryType,
                'infringementDate' => $infringementDate,
                'checkDate' => $checkDate,
                'memberStateCode' => $memberStateCode,
                'reason' => $reason
            ]
        );

        $si = m::mock(SiEntity::class)->makePartial();
        $si->setId($id);
        $si->shouldReceive('getCase->isErru')->once()->andReturn(false);

        $savedSi = null;

        $this->repoMap['SeriousInfringement']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->once()
            ->andReturn($si)
            ->shouldReceive('save')
            ->with(m::type(SiEntity::class))
            ->once()
            ->andReturnUsing(
                function (SiEntity $si) use (&$savedSi) {
                    $savedSi = $si;
                }
            );

        $expected = [
            'id' => [
                'si' => $id,
            ],
            'messages' => [
                'Serious Infringement updated'
            ]
        ];

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(\DateTime::class, $savedSi->getCheckDate());
        $this->assertEquals($checkDate, $savedSi->getCheckDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $savedSi->getInfringementDate());
        $this->assertEquals($infringementDate, $savedSi->getInfringementDate()->format('Y-m-d'));
        $this->assertSame(
            $this->references[SiCategoryEntity::class][SiCategoryEntity::ERRU_DEFAULT_CATEGORY],
            $savedSi->getSiCategory()
        );
        $this->assertSame(
            $this->references[SiCategoryTypeEntity::class][$siCategoryType],
            $savedSi->getSiCategoryType()
        );
        $this->assertSame(
            $this->references[CountryEntity::class][$memberStateCode],
            $savedSi->getMemberStateCode()
        );
        $this->assertEquals($notificationNumber, $savedSi->getNotificationNumber());
        $this->assertEquals($reason, $savedSi->getReason());
    }

    public function testHandleCommandThrowsErruException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $id = 123;
        $version = 1;

        $si = m::mock(SiEntity::class);
        $si->shouldReceive('getCase->isErru')->once()->andReturn(true);

        $this->repoMap['SeriousInfringement']
            ->shouldReceive('fetchUsingId')
            ->andReturn($si);

        $data = [
            'id' => $id,
            'version' => $version,
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
