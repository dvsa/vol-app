<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\PermitApplicationReference as Sut;

/**
 * PermitApplicationReference bookmark test
 */
class PermitApplicationReferenceTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['irhpPermit' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new Sut();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'irhpPermitApplication' => [
                        'relatedApplication' => [
                            'applicationRef' => 'OB1234567/1',
                        ]
                    ]
                ],
                'OB1234567/1'
            ],
            [
                [],
                ''
            ],
        ];
    }
}
