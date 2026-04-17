<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\View;

use Dvsa\Olcs\Api\Entity\View\BusRegHistoryView;

/**
 * BusRegHistoryView
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusRegHistoryViewTest extends \PHPUnit\Framework\TestCase
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
            'busReg' => 1,
            'changeMadeBy' => 'Name Surname',
            'eventDatetime' => '2015-01-01',
            'eventData' => '123',
            'eventHistoryType' => 1,
            'user' => 2,
            'eventDescription' => '345'
        ];
        $this->entity = new BusRegHistoryView();

        $ref = new \ReflectionObject($this->entity);
        foreach (array_keys($this->testData) as $property) {
            $refProperty = $ref->getProperty($property);
            $refProperty->setValue($this->entity, $this->testData[$property]);
        }
    }

    public function testGetters(): void
    {
        foreach ($this->testData as $property => $value) {
            $getter = 'get' . ucfirst((string) $property);
            $this->assertEquals($value, $this->entity->$getter());
        }
    }
}
