<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\CreateSi as Sut;
use Dvsa\Olcs\Api\Domain\Repository\SeriousInfringement as SiRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\CreateSi as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CreateSi Test
 */
class CreateSiTest extends AbstractCommandHandlerTestCase
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
            CaseEntity::class => [
                1 => m::mock(CaseEntity::class)->makePartial()
            ],
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
        $caseId = 1;
        $notificationNumber = 'ABC123';
        $siCategoryType = 100;
        $infringementDate = '2015-12-25';
        $checkDate = '2015-12-26';
        $memberStateCode = 'GB';
        $reason = 'reason';

        $command = Cmd::Create(
            [
                'case' => $caseId,
                'notificationNumber' => $notificationNumber,
                'siCategoryType' => $siCategoryType,
                'infringementDate' => $infringementDate,
                'checkDate' => $checkDate,
                'memberStateCode' => $memberStateCode,
                'reason' => $reason
            ]
        );

        $savedSi = null;

        $this->repoMap['SeriousInfringement']
            ->shouldReceive('save')
            ->with(m::type(SiEntity::class))
            ->once()
            ->andReturnUsing(
                function (SiEntity $si) use (&$savedSi) {
                    $si->setId(111);
                    $savedSi = $si;
                }
            );

        $expected = [
            'id' => [
                'si' => 111,
            ],
            'messages' => [
                'Serious Infringement created'
            ]
        ];

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[CaseEntity::class][$caseId], $savedSi->getCase());
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

        $caseId = 1;

        $data = [
            'case' => $caseId,
        ];

        $this->references[CaseEntity::class][$caseId]->shouldReceive('isErru')->once()->andReturn(true);

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
