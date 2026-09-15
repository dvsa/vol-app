<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\EventHistoryUser;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Event history user formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EventHistoryUserTest extends MockeryTestCase
{
    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new EventHistoryUser($this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expectedOutput): void
    {
        if (isset($data['user']['team'])) {
            $this->translator
                ->shouldReceive('translate')
                ->with('internal.marker')
                ->andReturn('(internal)')
                ->once();
        }

        $this->assertEquals($expectedOutput, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'external with name' => [
            [
                'user' => [
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'Foo',
                            'familyName' => 'Bar'
                        ]
                    ]
                ],
                'changeMadeBy' => null,
            ],
            'Foo Bar',
        ];
        yield 'internal with name' => [
            [
                'user' => [
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'Foo',
                            'familyName' => 'Bar'
                        ]
                    ],
                    'team' => 'some team'
                ],
                'changeMadeBy' => null,
            ],
            'Foo Bar (internal)',
        ];
        yield 'external with no name' => [
            [
                'user' => [
                    'loginId' => 'cake'
                ],
                'changeMadeBy' => null,
            ],
            'cake',
        ];
        yield 'internal with no name' => [
            [
                'user' => [
                    'loginId' => 'cake',
                    'team' => 'some team'
                ],
                'changeMadeBy' => null,
            ],
            'cake (internal)',
        ];
        yield 'internal with error' => [
            [
                'user' => [
                    'contactDetails' => [
                        'person' => 'something wrong here'
                    ],
                    'team' => 'some team',
                    'loginId' => 'cake'
                ],
                'changeMadeBy' => null,
            ],
            'cake (internal)',
        ];
        yield 'external with change made by' => [
            [
                'user' => [
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'Foo',
                            'familyName' => 'Bar'
                        ]
                    ]
                ],
                'changeMadeBy' => 'Name Surname',
            ],
            'Name Surname',
        ];
        yield 'internal with change made by' => [
            [
                'user' => [
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'Foo',
                            'familyName' => 'Bar'
                        ]
                    ],
                    'team' => 'some team'
                ],
                'changeMadeBy' => 'Name Surname',
            ],
            'Name Surname (internal)',
        ];
    }
}
