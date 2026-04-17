<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Validator;

use Dvsa\Olcs\Api\Service\Nr\Validator\SiPenaltyImposedDate;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SiPenaltyImposedDateTest
 * @package Dvsa\OlcsTest\Api\Service\Nr\Validator
 */
class SiPenaltyImposedDateTest extends TestCase
{
    /**
     * @param $imposedErrus
     * @param $valid
     * @param string $error
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
    public function testIsValid(mixed $imposedErrus, mixed $valid, string $error = ''): void
    {
        $value = ['imposedErrus' => [0 => $imposedErrus]];

        $sut = new SiPenaltyImposedDate();

        $this->assertEquals($valid, $sut->isValid($value));

        if ($error != '') {
            $message = current($sut->getMessages());
            $this->assertEquals($error, $message);
        }
    }

    public static function provideIsValid(): array
    {
        return [
            [
                [
                    'finalDecisionDate' => new \DateTime('2012-05-16 00:00:00'),
                    'startDate' => new \DateTime('2013-05-16 00:00:00'),
                    'endDate' => new \DateTime('2014-05-16 00:00:00'),
                ],
                true
            ],
            [
                [
                    'finalDecisionDate' => new \DateTime('2014-05-16 00:00:00'),
                    'startDate' => new \DateTime('2013-05-16 00:00:00'),
                    'endDate' => new \DateTime('2014-05-16 00:00:00'),
                ],
                false,
                'Imposed penalty decision date later than start date'
            ],
            [
                [
                    'finalDecisionDate' => new \DateTime('2013-05-16 00:00:00'),
                    'startDate' => new \DateTime('2015-05-16 00:00:00'),
                    'endDate' => new \DateTime('2014-05-16 00:00:00'),
                ],
                false,
                'Imposed penalty start date must be before end date'
            ],
            [
                [
                    'finalDecisionDate' => new \DateTime('2015-05-16 00:00:00'),
                    'startDate' => null,
                    'endDate' => new \DateTime('2014-05-16 00:00:00'),
                ],
                false,
                'Imposed penalty decision date later than end date'
            ],
        ];
    }
}
