<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Stay as Entity;

/**
 * Stay Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class StayEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @param array $valuesForEntity          Values to set into Entity
     * @param array $expectedValuesFromEntity Expected values from Entity
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('stayValuesDataProvided')]
    public function testValuesSetReturnsExpectedEntity(
        mixed $valuesForEntity,
        mixed $expectedValuesFromEntity
    ): void {
        /** @var Cases $case */
        $case = $this->createStub(Cases::class);

        /** @var RefData $stayType */
        $stayType = $this->createStub(RefData::class);

        $stayEntity = new Entity($case, $stayType);
        $stayEntity->values(
            $valuesForEntity['requestDate'],
            $valuesForEntity['decisionDate'],
            $valuesForEntity['outcome'],
            $valuesForEntity['notes'],
            $valuesForEntity['isWithdrawn'],
            $valuesForEntity['withdrawnDate'],
            $valuesForEntity['isDvsaNotified']
        );

        $this->assertEquals(
            $expectedValuesFromEntity['requestDate'],
            $stayEntity->getRequestDate()
        );

        $this->assertEquals(
            $expectedValuesFromEntity['decisionDate'],
            $stayEntity->getDecisionDate()
        );

        $this->assertEquals(
            $expectedValuesFromEntity['outcome'],
            $stayEntity->getOutcome()
        );

        $this->assertEquals(
            $expectedValuesFromEntity['notes'],
            $stayEntity->getNotes()
        );

        $this->assertEquals(
            $expectedValuesFromEntity['withdrawnDate'],
            $stayEntity->getWithdrawnDate()
        );

        $this->assertEquals(
            $expectedValuesFromEntity['isDvsaNotified'],
            $stayEntity->getDvsaNotified()
        );
    }

    /**
     * Add in Values to pass into the Entity values() method
     *
     * @return array
     */
    public static function stayValuesDataProvided(): array
    {
        return [
            'all values null to return null' => [
                [
                    'requestDate'    => null,
                    'decisionDate'   => null,
                    'outcome'        => null,
                    'notes'          => null,
                    'isWithdrawn'    => null,
                    'withdrawnDate'  => null,
                    'isDvsaNotified' => null,
                ],
                [
                    'requestDate'    => null,
                    'decisionDate'   => null,
                    'outcome'        => null,
                    'notes'          => null,
                    'withdrawnDate'  => null,
                    'isDvsaNotified' => null,
                ],
            ],
            'set all values with but not withdrawn' => [
                [
                    'requestDate'    => new \DateTime('2017-01-01'),
                    'decisionDate'   => '2017-01-02',
                    'outcome'        => new RefData(1),
                    'notes'          => '123456',
                    'isWithdrawn'    => null,
                    'withdrawnDate'  => null,
                    'isDvsaNotified' => 'N',
                ],
                [
                    'requestDate'    => new \DateTime('2017-01-01'),
                    'decisionDate'   => new \DateTime('2017-01-02'),
                    'outcome'        => new RefData(1),
                    'notes'          => '123456',
                    'withdrawnDate'  => null,
                    'isDvsaNotified' => 'N',
                ],
            ],
            'set all values with with withdrawn date but is not withdrawn' => [
                [
                    'requestDate'    => new \DateTime('2017-01-01'),
                    'decisionDate'   => '2017-01-02',
                    'outcome'        => new RefData(1),
                    'notes'          => '123456',
                    'isWithdrawn'    => 'N',
                    'withdrawnDate'  => '2017-01-03',
                    'isDvsaNotified' => 'N',
                ],
                [
                    'requestDate'    => new \DateTime('2017-01-01'),
                    'decisionDate'   => new \DateTime('2017-01-02'),
                    'outcome'        => new RefData(1),
                    'notes'          => '123456',
                    'withdrawnDate'  => null,
                    'isDvsaNotified' => 'N',
                ],
            ],
            'set all values with withdrawn date and is withdrawn' => [
                [
                    'requestDate'    => new \DateTime('2017-01-01'),
                    'decisionDate'   => '2017-01-02',
                    'outcome'        => new RefData(1),
                    'notes'          => '123456',
                    'isWithdrawn'    => 'Y',
                    'withdrawnDate'  => '2017-01-03',
                    'isDvsaNotified' => 'N',
                ],
                [
                    'requestDate'    => new \DateTime('2017-01-01'),
                    'decisionDate'   => new \DateTime('2017-01-02'),
                    'outcome'        => new RefData(1),
                    'notes'          => '123456',
                    'withdrawnDate'  => new \DateTime('2017-01-03'),
                    'isDvsaNotified' => 'N',
                ],
            ],
        ];
    }
}
