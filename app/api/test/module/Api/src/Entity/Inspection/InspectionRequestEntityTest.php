<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Inspection;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest as Entity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * InspectionRequest Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class InspectionRequestEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('datesProvider')]
    public function testUpdateInspectionRequest(mixed $dueDate, mixed $duePeriod, mixed $dueExpected, mixed $requestDate, mixed $requestDateExpected): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateInspectionRequest(
            'req_type',
            $requestDate,
            $dueDate,
            $duePeriod,
            'res_type',
            'requestor_notes',
            'rep_type',
            1,
            2,
            3,
            4,
            'insp_name',
            '01/01/2015',
            '01/01/2016',
            '01/01/2017',
            5,
            6,
            'inspector_notes'
        );
        $this->assertEquals('req_type', $sut->getRequestType());
        $this->assertEquals($requestDateExpected, $sut->getRequestDate());
        $this->assertEquals($dueExpected, $sut->getDueDate());
        $this->assertEquals('res_type', $sut->getResultType());
        $this->assertEquals('requestor_notes', $sut->getRequestorNotes());
        $this->assertEquals('rep_type', $sut->getReportType());
        $this->assertEquals(1, $sut->getApplication());
        $this->assertEquals(2, $sut->getLicence());
        $this->assertEquals(3, $sut->getRequestorUser());
        $this->assertEquals(4, $sut->getOperatingCentre());
        $this->assertEquals('insp_name', $sut->getInspectorName());
        $this->assertEquals(new \DateTime('01/01/2015'), $sut->getReturnDate());
        $this->assertEquals(new \DateTime('01/01/2016'), $sut->getFromDate());
        $this->assertEquals(new \DateTime('01/01/2017'), $sut->getToDate());
        $this->assertEquals(5, $sut->getVehiclesExaminedNo());
        $this->assertEquals(6, $sut->getTrailersExaminedNo());
        $this->assertEquals('inspector_notes', $sut->getInspectorNotes());
    }

    public static function datesProvider(): array
    {
        return [
            [null, 3, (new DateTime('now'))->add(new \DateInterval('P3M')), null, new DateTime('now')],
            ['2015-01-01', null, new \DateTime('2015-01-01'), '2014-01-01', new \DateTime('2014-01-01')]
        ];
    }

    public function testUpdateInspectionRequestNotValid(): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();

        try {
            $sut->updateInspectionRequest(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            );
        } catch (ValidationException $e) {
            static::assertEquals(
                $e->getMessages(),
                [
                    [
                        'reportType' => [Entity::ERROR_FIELD_IS_REQUIRED => 'Field is required'],
                    ],
                    [
                        'resultType' => [Entity::ERROR_FIELD_IS_REQUIRED => 'Field is required'],
                    ],
                    [
                        'dueDate' => [Entity::ERROR_FIELD_IS_REQUIRED => 'Field is required'],
                    ],
                    [
                        'dueDate' => [Entity::ERROR_DUE_DATE => 'Due date should be the same or after date requested'],
                    ],
                ]
            );
        }
    }

    public function testUpdateInspectionRequestDueDateNotInRange(): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();

        try {
            $sut->updateInspectionRequest(
                null,
                null,
                null,
                1,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            );
        } catch (ValidationException $e) {
            static::assertEquals(
                $e->getMessages(),
                [
                    [
                        'reportType' => [Entity::ERROR_FIELD_IS_REQUIRED => 'Field is required'],
                    ],
                    [
                        'resultType' => [Entity::ERROR_FIELD_IS_REQUIRED => 'Field is required'],
                    ],
                    [
                        'dueDate' => [Entity::ERROR_DUE_DATE_NOT_IN_RANGE => 'Due date not in range'],
                    ],
                ]
            );
        }
    }

    public function testUpdateInspectionRequestRequestDateInFuture(): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();

        try {
            $sut->updateInspectionRequest(
                'req_type',
                '2222-01-01',
                null,
                3,
                'res_type',
                'requestor_notes',
                'rep_type',
                1,
                2,
                3,
                4,
                'insp_name',
                '01/01/2015',
                '01/01/2016',
                '01/01/2017',
                5,
                6,
                'inspector_notes'
            );
        } catch (ValidationException $e) {
            static::assertEquals(
                $e->getMessages(),
                [
                    'requestDate' => [
                        Entity::ERROR_REQUEST_DATE_IN_FUTURE => 'Request date should not be in future',
                    ],
                ]
            );
        }
    }
}
