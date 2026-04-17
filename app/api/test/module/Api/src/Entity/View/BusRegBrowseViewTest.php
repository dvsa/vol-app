<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\View;

use Dvsa\Olcs\Api\Entity\View\BusRegBrowseView;

/**
 * BusRegBrowseView
 */
class BusRegBrowseViewTest extends \PHPUnit\Framework\TestCase
{
    protected $entity;

    /**
     * @var array
     */
    protected $testData;

    public function setUp(): void
    {
        $this->testData = [
            'id' => 15,
            'trafficAreaId' => 'C',
            'trafficAreaName' => 'TA name',
            'name' => 'Name',
            'address' => 'Address',
            'licNo' => 'lic no',
            'licStatus' => 'lic status',
            'regNo' => 'reg no',
            'brStatus' => 'bus reg status',
            'busServiceType' => 'bus service type',
            'variationNo' => 1,
            'receivedDate' => '2015-01-01',
            'effectiveDate' => '2015-01-02',
            'endDate' => '2015-01-03',
            'serviceNo' => 'service no',
            'isShortNotice' => true,
            'startPoint' => 'start point',
            'finishPoint' => 'finish point',
            'via' => 'via',
            'otherDetails' => 'other details',
            'acceptedDate' => '2015-01-04',
            'eventDescription' => 'event description',
            'eventRegistrationStatus' => 'event registration status',
            'status' => 'status',
        ];
        $this->entity = new BusRegBrowseView();

        $ref = new \ReflectionObject($this->entity);
        foreach (array_keys($this->testData) as $property) {
            $refProperty = $ref->getProperty($property);
            $refProperty->setValue($this->entity, $this->testData[$property]);
        }
    }

    public function testGetters(): void
    {
        foreach ($this->testData as $property => $value) {
            $getter = ($property === 'isShortNotice') ? $property : 'get' . ucfirst((string) $property);
            $this->assertEquals($value, $this->entity->$getter());
        }
    }
}
