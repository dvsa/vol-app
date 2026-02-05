<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\FeeDueDate;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\FeeDueDate
 */
class FeeDueDateTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new FeeDueDate();
        $query = $bookmark->getQuery(['fee' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestRender')]
    public function testRender(mixed $invoicedDate, mixed $atCalculate): void
    {
        $dateTime = new \DateTime('2001-02-03');

        /** @var \Dvsa\Olcs\Api\Service\Date $dateHelper */
        $dateHelper = m::mock(\Dvsa\Olcs\Api\Service\Date::class)
            ->shouldReceive('calculateDate')
            ->with(m::mustBe($atCalculate), FeeDueDate::TARGET_DAYS)
            ->once()
            ->andReturn($dateTime)
            ->getMock();

        $bookmark = new FeeDueDate();
        $bookmark->setData(['invoicedDate' => $invoicedDate]);
        $bookmark->setDateHelper($dateHelper);

        //  call
        $this->assertEquals('03/02/2001', $bookmark->render());
    }

    public static function dpTestRender(): array
    {
        return [
            [
                'invoicedDate' => '2003-02-01',
                'atCalculate' => new \DateTime('2003-02-01'),
            ],
            [
                'invoicedDate' => new \DateTime('2013-12-11'),
                'atCalculate' => new \DateTime('2013-12-11'),
            ],
        ];
    }
}
