<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Bus;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as Entity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Mockery as m;

/**
 * BusShortNotice Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class BusShortNoticeEntityTest extends EntityTester
{
    public function setUp(): void
    {
        /** @var Entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests calculated values
     */
    public function testGetCalculatedBundleValues(): void
    {
        $isLatestVariation = true;

        //the bus reg entity related to short notice
        $busRegMock = m::mock(BusRegEntity::class);
        $busRegMock->shouldReceive('isLatestVariation')->once()->andReturn($isLatestVariation);

        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getBusReg')->once()->andReturn($busRegMock);

        $result = $sut->getCalculatedBundleValues();

        $this->assertEquals($result['isLatestVariation'], true);
    }

    /**
     * Test exception is thrown when edit not allowed
     */
    public function testUpdateThrowsException(): void
    {
        $this->expectException(ForbiddenException::class);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->expects('canEdit')->andThrow(ForbiddenException::class);

        $this->entity->setBusReg($busReg);

        $bankHolidayChange = 'Y';
        $unforseenChange = 'Y';
        $unforseenDetail = 'unforseen detail';
        $timetableChange =
        $timetableDetail = 'timetable detail';
        $replacementChange = 'Y';
        $replacementDetail = 'replacement detail';
        $notAvailableChange = 'Y';
        $notAvailableDetail = 'not available detail';
        $specialOccasionChange = 'N';
        $specialOccasionDetail = 'special occasion detail';
        $connectionChange = 'N';
        $connectionDetail = 'connection detail';
        $holidayChange = 'N';
        $holidayDetail = 'holiday detail';
        $trcChange = 'N';
        $trcDetail = 'trc detail';
        $policeChange = 'N';
        $policeDetail = 'police detail';

        $this->entity->update(
            $bankHolidayChange,
            $unforseenChange,
            $unforseenDetail,
            $timetableChange,
            $timetableDetail,
            $replacementChange,
            $replacementDetail,
            $notAvailableChange,
            $notAvailableDetail,
            $specialOccasionChange,
            $specialOccasionDetail,
            $connectionChange,
            $connectionDetail,
            $holidayChange,
            $holidayDetail,
            $trcChange,
            $trcDetail,
            $policeChange,
            $policeDetail
        );
    }

    /**
     * Tests the update function
     */
    public function testUpdate(): void
    {
        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('canEdit')
            ->once()
            ->andReturn(true);

        $this->entity->setBusReg($busReg);

        $bankHolidayChange = 'Y';
        $unforseenChange = 'Y';
        $unforseenDetail = 'unforseen detail';
        $timetableChange =
        $timetableDetail = 'timetable detail';
        $replacementChange = 'Y';
        $replacementDetail = 'replacement detail';
        $notAvailableChange = 'Y';
        $notAvailableDetail = 'not available detail';
        $specialOccasionChange = 'N';
        $specialOccasionDetail = 'special occasion detail';
        $connectionChange = 'N';
        $connectionDetail = 'connection detail';
        $holidayChange = 'N';
        $holidayDetail = 'holiday detail';
        $trcChange = 'N';
        $trcDetail = 'trc detail';
        $policeChange = 'N';
        $policeDetail = 'police detail';

        $this->entity->update(
            $bankHolidayChange,
            $unforseenChange,
            $unforseenDetail,
            $timetableChange,
            $timetableDetail,
            $replacementChange,
            $replacementDetail,
            $notAvailableChange,
            $notAvailableDetail,
            $specialOccasionChange,
            $specialOccasionDetail,
            $connectionChange,
            $connectionDetail,
            $holidayChange,
            $holidayDetail,
            $trcChange,
            $trcDetail,
            $policeChange,
            $policeDetail
        );

        $this->assertEquals($bankHolidayChange, $this->entity->getBankHolidayChange());
        $this->assertEquals($unforseenChange, $this->entity->getUnforseenChange());
        $this->assertEquals($unforseenDetail, $this->entity->getUnforseenDetail());
        $this->assertEquals($timetableChange, $this->entity->getTimetableChange());
        $this->assertEquals($timetableDetail, $this->entity->getTimetableDetail());
        $this->assertEquals($replacementChange, $this->entity->getReplacementChange());
        $this->assertEquals($replacementDetail, $this->entity->getReplacementDetail());
        $this->assertEquals($notAvailableChange, $this->entity->getNotAvailableChange());
        $this->assertEquals($notAvailableDetail, $this->entity->getNotAvailableDetail());
        $this->assertEquals($specialOccasionChange, $this->entity->getSpecialOccasionChange());
        $this->assertEquals($specialOccasionDetail, $this->entity->getSpecialOccasionDetail());
        $this->assertEquals($connectionChange, $this->entity->getConnectionChange());
        $this->assertEquals($connectionDetail, $this->entity->getConnectionDetail());
        $this->assertEquals($holidayChange, $this->entity->getHolidayChange());
        $this->assertEquals($holidayDetail, $this->entity->getHolidayDetail());
        $this->assertEquals($trcChange, $this->entity->getTrcChange());
        $this->assertEquals($trcDetail, $this->entity->getTrcDetail());
        $this->assertEquals($policeChange, $this->entity->getPoliceChange());
        $this->assertEquals($policeDetail, $this->entity->getPoliceDetail());
    }

    /**
     * Tests the reset function
     */
    public function testReset(): void
    {
        $this->entity->setBankHolidayChange('Y');
        $this->entity->setUnforseenChange('Y');
        $this->entity->setUnforseenDetail('unforseen detail');
        $this->entity->setTimetableChange('Y');
        $this->entity->setTimetableDetail('timetable detail');
        $this->entity->setReplacementChange('Y');
        $this->entity->setReplacementDetail('replacement detail');
        $this->entity->setNotAvailableChange('Y');
        $this->entity->setNotAvailableDetail('not available detail');
        $this->entity->setSpecialOccasionChange('N');
        $this->entity->setSpecialOccasionDetail('special occasion detail');
        $this->entity->setConnectionChange('N');
        $this->entity->setConnectionDetail('connection detail');
        $this->entity->setHolidayChange('N');
        $this->entity->setHolidayDetail('holiday detail');
        $this->entity->setTrcChange('N');
        $this->entity->setTrcDetail('trc detail');
        $this->entity->setPoliceChange('N');
        $this->entity->setPoliceDetail('police detail');

        $this->entity->reset();

        $this->assertEquals('N', $this->entity->getBankHolidayChange());
        $this->assertEquals('N', $this->entity->getUnforseenChange());
        $this->assertEquals(null, $this->entity->getUnforseenDetail());
        $this->assertEquals('N', $this->entity->getTimetableChange());
        $this->assertEquals(null, $this->entity->getTimetableDetail());
        $this->assertEquals('N', $this->entity->getReplacementChange());
        $this->assertEquals(null, $this->entity->getReplacementDetail());
        $this->assertEquals('N', $this->entity->getNotAvailableChange());
        $this->assertEquals(null, $this->entity->getNotAvailableDetail());
        $this->assertEquals('N', $this->entity->getSpecialOccasionChange());
        $this->assertEquals(null, $this->entity->getSpecialOccasionDetail());
        $this->assertEquals('N', $this->entity->getConnectionChange());
        $this->assertEquals(null, $this->entity->getConnectionDetail());
        $this->assertEquals('N', $this->entity->getHolidayChange());
        $this->assertEquals(null, $this->entity->getHolidayDetail());
        $this->assertEquals('N', $this->entity->getTrcChange());
        $this->assertEquals(null, $this->entity->getTrcDetail());
        $this->assertEquals('N', $this->entity->getPoliceChange());
        $this->assertEquals(null, $this->entity->getPoliceDetail());
    }

    public function testFromData(): void
    {
        $this->entity->fromData(['connectionDetail' => 'foo', 'holidayDetail' => 'bar']);
        $this->assertEquals($this->entity->getConnectionDetail(), 'foo');
        $this->assertEquals($this->entity->getHolidayDetail(), 'bar');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('hasGrantableDetailsProvider')]
    public function testHasGrantableDetails(mixed $change, mixed $details, mixed $expected): void
    {
        $this->entity->setBankHolidayChange($change['bankHolidayChange']);
        $this->entity->setConnectionChange($change['connectionChange']);
        $this->entity->setHolidayChange($change['holidayChange']);
        $this->entity->setNotAvailableChange($change['notAvailableChange']);
        $this->entity->setPoliceChange($change['policeChange']);
        $this->entity->setReplacementChange($change['replacementChange']);
        $this->entity->setSpecialOccasionChange($change['specialOccasionChange']);
        $this->entity->setTimetableChange($change['timetableChange']);
        $this->entity->setTrcChange($change['trcChange']);
        $this->entity->setUnforseenChange($change['unforseenChange']);

        $this->entity->setConnectionDetail($details['connectionDetail']);
        $this->entity->setHolidayDetail($details['holidayDetail']);
        $this->entity->setNotAvailableDetail($details['notAvailableDetail']);
        $this->entity->setPoliceDetail($details['policeDetail']);
        $this->entity->setReplacementDetail($details['replacementDetail']);
        $this->entity->setSpecialOccasionDetail($details['specialOccasionDetail']);
        $this->entity->setTimetableDetail($details['timetableDetail']);
        $this->entity->setTrcDetail($details['trcDetail']);
        $this->entity->setUnforseenDetail($details['unforseenDetail']);

        $this->assertEquals($this->entity->hasGrantableDetails(), $expected);
    }

    public static function hasGrantableDetailsProvider(): array
    {
        return [
            [
                [
                    'bankHolidayChange' => 'N',
                    'connectionChange' => 'N',
                    'holidayChange' => 'N',
                    'notAvailableChange' => 'N',
                    'policeChange' => 'N',
                    'replacementChange' => 'N',
                    'specialOccasionChange' => 'N',
                    'timetableChange' => 'N',
                    'trcChange' => 'N',
                    'unforseenChange' => 'N'
                ],
                [
                    'connectionDetail' => '',
                    'holidayDetail' => '',
                    'notAvailableDetail' => '',
                    'policeDetail' => '',
                    'replacementDetail' => '',
                    'specialOccasionDetail' => '',
                    'timetableDetail' => '',
                    'trcDetail' => '',
                    'unforseenDetail' => ''
                ],
                false
            ],
            [
                [
                    'bankHolidayChange' => 'Y',
                    'connectionChange' => 'N',
                    'holidayChange' => 'N',
                    'notAvailableChange' => 'N',
                    'policeChange' => 'N',
                    'replacementChange' => 'N',
                    'specialOccasionChange' => 'N',
                    'timetableChange' => 'N',
                    'trcChange' => 'N',
                    'unforseenChange' => 'N'
                ],
                [
                    'connectionDetail' => '',
                    'holidayDetail' => '',
                    'notAvailableDetail' => '',
                    'policeDetail' => '',
                    'replacementDetail' => '',
                    'specialOccasionDetail' => '',
                    'timetableDetail' => '',
                    'trcDetail' => '',
                    'unforseenDetail' => ''
                ],
                true
            ],
            [
                [
                    'bankHolidayChange' => 'N',
                    'connectionChange' => 'Y',
                    'holidayChange' => 'N',
                    'notAvailableChange' => 'N',
                    'policeChange' => 'N',
                    'replacementChange' => 'N',
                    'specialOccasionChange' => 'N',
                    'timetableChange' => 'N',
                    'trcChange' => 'N',
                    'unforseenChange' => 'N'
                ],
                [
                    'connectionDetail' => 'foo',
                    'holidayDetail' => '',
                    'notAvailableDetail' => '',
                    'policeDetail' => '',
                    'replacementDetail' => '',
                    'specialOccasionDetail' => '',
                    'timetableDetail' => '',
                    'trcDetail' => '',
                    'unforseenDetail' => ''
                ],
                true
            ],
            [
                [
                    'bankHolidayChange' => 'N',
                    'connectionChange' => 'Y',
                    'holidayChange' => 'N',
                    'notAvailableChange' => 'N',
                    'policeChange' => 'N',
                    'replacementChange' => 'N',
                    'specialOccasionChange' => 'N',
                    'timetableChange' => 'N',
                    'trcChange' => 'N',
                    'unforseenChange' => 'N'
                ],
                [
                    'connectionDetail' => '',
                    'holidayDetail' => '',
                    'notAvailableDetail' => '',
                    'policeDetail' => '',
                    'replacementDetail' => '',
                    'specialOccasionDetail' => '',
                    'timetableDetail' => '',
                    'trcDetail' => '',
                    'unforseenDetail' => ''
                ],
                false
            ]
        ];
    }
}
