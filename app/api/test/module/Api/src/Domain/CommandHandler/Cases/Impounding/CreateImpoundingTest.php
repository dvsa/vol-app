<?php

declare(strict_types=1);

/**
 * Create Impounding Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Impounding;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Impounding\CreateImpounding;
use Dvsa\Olcs\Api\Domain\Repository\Impounding;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\CreateImpounding as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Domain\Command\Publication\Impounding as PublishImpoundingCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create Impounding Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateImpoundingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateImpounding();
        $this->mockRepo('Impounding', Impounding::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'impt_hearing',
            CasesEntity::APP_CASE_TYPE,
            CasesEntity::LICENCE_CASE_TYPE,
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            LicenceEntity::LICENCE_CATEGORY_PSV,
        ];

        $this->references = [
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)->makePartial()
            ],
            VenueEntity::class => [
                8 => m::mock(VenueEntity::class)->makePartial()
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class)->makePartial()
            ],
            ApplicationEntity::class => [
                1 => m::mock(ApplicationEntity::class)->makePartial()
            ],
            TrafficAreaEntity::class => [
                'B' => m::mock(TrafficAreaEntity::class)->makePartial()
            ],
            PiEntity::class => [
                77 => m::mock(PiEntity::class)->makePartial()
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithoutPublish(): void
    {
        $command = Cmd::create(
            [
            'id' => 5,
            'version' => 4,
            'case' => 24,
            'applicationReceiptDate' => '2014-06-09',
            'closeDate' => '2015-05-28T10:53:34+0100',
            'createdOn' => '2015-05-28T10:53:34+0100',
            'hearingDate' => '2014-06-10T15:45:00+0100',
            'lastModifiedOn' => '2015-05-28T10:53:34+0100',
            'notes' => 'Some notes - db default',
            'outcomeSentDate' => '2014-06-11',
            'venueOther' => null,
            'venue' => 8,
            'vrm' => 'vrm1',
            'impoundingLegislationTypes' => [
                'imlgis_type_goods_ni1',
                'imlgis_type_goods_ni2'
            ],
            'impoundingType' => 'impt_hearing',
            'outcome' => 'impo_returned',
            'presidingTc' => 1
            ]
        );

        /** @var ImpoundingEntity $imp */
        $imp = null;

        $this->repoMap['Impounding']
            ->shouldReceive('save')
            ->with(m::type(ImpoundingEntity::class))
            ->andReturnUsing(
                function (ImpoundingEntity $impounding) use (&$imp) {
                    $imp = $impounding;
                    $impounding->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Impounding created', $result->getMessages());

        $this->assertEquals('vrm1', $imp->getVrm());
    }

    /**
     * @param $licType Goods or PSV
     * @param $commandData Expected Command Data for publish
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideImpoundingLicencePublishCommands')]
    public function testHandleCommandWithPublishForLicenceCases(mixed $licType, mixed $commandData): void
    {
        $command = Cmd::create(
            [
                'id' => 5,
                'version' => 4,
                'case' => 24,
                'impoundingType' => 'impt_hearing',
                'publish' => 'Y'
            ]
        );

        $this->references[CasesEntity::class][24]->shouldReceive('getCaseType')
            ->andReturn($this->refData[CasesEntity::LICENCE_CASE_TYPE])
            ->shouldReceive('getLicence')
            ->andReturn($this->references[LicenceEntity::class][7]);

        $this->references[TrafficAreaEntity::class]['B']->shouldReceive('getId')
            ->andReturn('B');

        $this->references[LicenceEntity::class][7]->shouldReceive('getTrafficArea')
            ->andReturn($this->references[TrafficAreaEntity::class]['B']);

        $this->references[LicenceEntity::class][7]->shouldReceive('getGoodsOrPsv->getId')
            ->andReturn($licType);

        /** @var ImpoundingEntity $imp */
        $imp = null;

        $this->repoMap['Impounding']
            ->shouldReceive('save')
            ->with(m::type(ImpoundingEntity::class))
            ->andReturnUsing(
                function (ImpoundingEntity $impounding) use (&$imp) {
                    $imp = $impounding;
                    $impounding->setId(99);
                }
            )
            ->once();

        $this->expectedSideEffect(
            PublishImpoundingCmd::class,
            $commandData,
            (new Result())->addMessage('Impounding published')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Impounding created', $result->getMessages());
        $this->assertContains('Impounding published', $result->getMessages());
    }

    /**
     * @param $licType Goods or PSV
     * @param $commandData Expected Command Data for publish
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideImpoundingApplicationPublishCommands')]
    public function testHandleCommandWithPublishForApplicationCases(mixed $licType, mixed $commandData): void
    {
        $command = Cmd::create(
            [
                'id' => 5,
                'version' => 4,
                'case' => 24,
                'impoundingType' => 'impt_hearing',
                'publish' => 'Y'
            ]
        );

        $this->references[ApplicationEntity::class][1]->shouldReceive('getLicence')
            ->andReturn($this->references[LicenceEntity::class][7]);

        $this->references[CasesEntity::class][24]->shouldReceive('getCaseType')
            ->andReturn($this->refData[CasesEntity::APP_CASE_TYPE])
            ->shouldReceive('getApplication')
            ->andReturn($this->references[ApplicationEntity::class][1])
            ->shouldReceive('getPublicInquiry')
            ->andReturn($this->references[PiEntity::class][77]);

        $this->references[TrafficAreaEntity::class]['B']->shouldReceive('getId')
            ->andReturn('B');

        $this->references[LicenceEntity::class][7]->shouldReceive('getTrafficArea')
            ->andReturn($this->references[TrafficAreaEntity::class]['B']);

        $this->references[ApplicationEntity::class][1]->shouldReceive('getGoodsOrPsv->getId')
            ->andReturn($licType);

        /** @var ImpoundingEntity $imp */
        $imp = null;

        $this->repoMap['Impounding']
            ->shouldReceive('save')
            ->with(m::type(ImpoundingEntity::class))
            ->andReturnUsing(
                function (ImpoundingEntity $impounding) use (&$imp) {
                    $imp = $impounding;
                    $impounding->setId(99);
                }
            )
            ->once();

        $this->expectedSideEffect(
            PublishImpoundingCmd::class,
            $commandData,
            (new Result())->addMessage('Impounding published')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Impounding created', $result->getMessages());
        $this->assertContains('Impounding published', $result->getMessages());
    }

    /**
     * Provides licence type and expected publish command data for cases attached to licence
     * @return array
     */
    public static function provideImpoundingLicencePublishCommands(): array
    {
        return [
            [
                LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                [
                    'id' => 99,
                    'trafficArea' => 'B',
                    'pi' => null,
                    'pubType' => 'A&D',
                    'licence' => 7
                ]
            ],
            [
                LicenceEntity::LICENCE_CATEGORY_PSV,
                [
                    'id' => 99,
                    'trafficArea' => 'B',
                    'pi' => null,
                    'pubType' => 'N&P',
                    'licence' => 7
                ]
            ]
        ];
    }

    /**
     * Provides licence type and expected publish command data for cases attached to application
     * @return array
     */
    public static function provideImpoundingApplicationPublishCommands(): array
    {
        return [
            [
                LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                [
                    'id' => 99,
                    'trafficArea' => 'B',
                    'pi' => 77,
                    'pubType' => 'A&D',
                    'application' => 1,
                    'licence' => null
                ]
            ],
            [
                LicenceEntity::LICENCE_CATEGORY_PSV,
                [
                    'id' => 99,
                    'trafficArea' => 'B',
                    'pi' => 77,
                    'pubType' => 'N&P',
                    'application' => 1,
                    'licence' => null
                ]
            ]
        ];
    }
}
