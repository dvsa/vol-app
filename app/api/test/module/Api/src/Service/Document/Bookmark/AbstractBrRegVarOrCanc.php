<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * AbstractBrRegVarOrCanc test
 */
class AbstractBrRegVarOrCanc extends \PHPUnit\Framework\TestCase
{
    protected const NEW_TEXT = '';
    protected const VARY_TEXT = '';
    protected const CANCEL_TEXT = '';
    protected $bookmarkClass;

    /**
     * test getQuery
     */
    public function testGetQuery(): void
    {
        $bookmark = $this->getBookmark();

        $this->assertInstanceOf($this->bookmarkClass, $bookmark);

        $this->assertInstanceOf(
            \Dvsa\Olcs\Transfer\Query\QueryInterface::class,
            $bookmark->getQuery(['busRegId' => 123])
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = $this->getBookmark();
        $bookmark->setData($data);
        $this->assertInstanceOf($this->bookmarkClass, $bookmark);

        if ($expected === false) {
            $this->expectException(
                \Exception::class
            );
        }
        $this->assertEquals($expected, $bookmark->render());
    }

    /**
     * @return array
     */
    public static function renderDataProvider(): array
    {
        return [
            [
                [],
                false
            ],
            [
                [
                    'status' => ''
                ],
                false
            ],
            [
                [
                    'status' => ['id' => 'foo'],
                ],
                false
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_NEW],
                ],
                static::NEW_TEXT
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_CANCEL],
                ],
                static::CANCEL_TEXT
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_VAR],
                ],
                static::VARY_TEXT
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_EXPIRED],
                ],
                false
            ],
        ];
    }

    /**
     * @return DynamicBookmark
     */
    public function getBookmark(): mixed
    {
        return new $this->bookmarkClass();
    }
}
