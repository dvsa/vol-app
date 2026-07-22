<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmTelephone as Sut;

/**
 * BkmTelephone bookmark test
 */
final class BkmTelephoneTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['organisation' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new Sut();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): \Iterator
    {
        yield [
            [
                'irfoContactDetails' => [
                    'phoneContacts' => [
                        [
                            'phoneContactType' => [
                                'id' => PhoneContactEntity::TYPE_PRIMARY
                            ],
                            'phoneNumber' => '1111'
                        ],
                        [
                            'phoneContactType' => [
                                'id' => PhoneContactEntity::TYPE_SECONDARY
                            ],
                            'phoneNumber' => '2222'
                        ],
                    ]
                ]
            ],
            '1111'
        ];
        yield [
            [
                'irfoContactDetails' => [
                    'phoneContacts' => [
                        [
                            'phoneContactType' => [
                                'id' => PhoneContactEntity::TYPE_SECONDARY
                            ],
                            'phoneNumber' => '2222'
                        ],
                    ]
                ]
            ],
            '2222'
        ];
        yield [
            [
                'irfoContactDetails' => [
                    'phoneContacts' => [
                    ]
                ]
            ],
            ''
        ];
        yield [
            [],
            ''
        ];
    }
}
