<?php

declare(strict_types=1);

/**
 * Update Impounding Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Impounding;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Impounding\UpdateImpounding;
use Dvsa\Olcs\Api\Domain\Repository\Impounding;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\UpdateImpounding as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Venue;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Update Impounding Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateImpoundingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateImpounding();
        $this->mockRepo('Impounding', Impounding::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'impt_hearing',
            'imlgis_type_goods_ni1',
            'imlgis_type_goods_ni2',
            'impo_returned'
        ];

        $this->references = [
            Cases::class => [
                24 => m::mock(Cases::class)
            ],
            Venue::class => [
                8 => m::mock(Venue::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $impoundingType = 'impt_hearing';
        $venue = 8;
        $vrm = 'AB14 CDE';
        $venueOther = 'venue other';
        $applicationReceiptDate = '2014-06-09';
        $hearingDate = '2014-06-10T15:45:00+0100';
        $notes = 'notes';
        $outcomeSentDate = '2015-06-01';

        $command = Cmd::Create(
            [
                'id' => 99,
                'version' => 1,
                'applicationReceiptDate' => $applicationReceiptDate,
                'hearingDate' => $hearingDate,
                'notes' => $notes,
                'outcomeSentDate' => $outcomeSentDate,
                'venueOther' => $venueOther,
                'venue' => $venue,
                'vrm' => $vrm,
                'impoundingLegislationTypes' => [
                    'imlgis_type_goods_ni1',
                    'imlgis_type_goods_ni2'
                ],
                'impoundingType' => $impoundingType,
                'outcome' => 'impo_returned',
                'presidingTc' => null
            ]
        );

        /** @var ImpoundingEntity $impounding */
        $impounding = m::mock(ImpoundingEntity::class);
        $impounding->shouldReceive('getId')->andReturn(99);
        $impounding->shouldReceive('update')->with(
            $this->refData['impt_hearing'],
            m::type(ArrayCollection::class),
            $this->references[Venue::class][$venue],
            $venueOther,
            $applicationReceiptDate,
            $vrm,
            $hearingDate,
            null,
            $this->refData['impo_returned'],
            $outcomeSentDate,
            $notes
        );

        /** @var ImpoundingEntity $imp */
        $imp = null;

        $this->repoMap['Impounding']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($impounding)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(ImpoundingEntity::class))
            ->andReturnUsing(
                function (ImpoundingEntity $impounding) use (&$imp) {
                    $imp = $impounding;
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Impounding updated', $result->getMessages());
    }
}
