<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\FlashMessenger;

/**
 * Date Test
 */
final class DateTimeTest extends MockeryTestCase
{
    /**
     * @var \Common\View\Helper\DateTime
     */
    private $sut;

    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new \Common\View\Helper\DateTime();

        date_default_timezone_set('UTC');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testInvoke(\DateTime $dateTime, string $format, string $expected): void
    {
        $sut = $this->sut;
        $this->assertEquals($expected, $sut($dateTime, $format));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), array<(\DateTime | string)>>
     *
     * @psalm-return list{list{\DateTime, 'd/m/Y H:i', '10/06/2016 12:00'}, list{\DateTime, 'd/m/Y H:i', '10/12/2016 12:00'}, list{\DateTime, 'd/m/Y H:i', '10/06/2016 11:00'}}
     */
    public static function provider(): \Iterator
    {
        yield [
            new \DateTime('2016-06-11 12:00', new \DateTimeZone('UTC')),
            'd/m/Y H:i',
            '11/06/2016 12:00'
        ];
        yield [
            new \DateTime('2016-12-12 12:00', new \DateTimeZone('UTC')),
            'd/m/Y H:i',
            '12/12/2016 12:00'
        ];
        yield [
            new \DateTime('2016-06-13 12:00', new \DateTimeZone('Europe/London')),
            'd/m/Y H:i',
            '13/06/2016 11:00'
        ];
    }
}
