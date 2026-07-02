<?php

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
class EventHistoryUserTest extends MockeryTestCase
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
     *
     * @dataProvider provider
     */
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
     * @return array
     */
    public function provider()
    {
        return [
            'external with name' => [
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
            ],
            'internal with name' => [
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
            ],
            'external with no name' => [
                [
                    'user' => [
                        'loginId' => 'cake'
                    ],
                    'changeMadeBy' => null,
                ],
                'cake',
            ],
            'internal with no name' => [
                [
                    'user' => [
                        'loginId' => 'cake',
                        'team' => 'some team'
                    ],
                    'changeMadeBy' => null,
                ],
                'cake (internal)',
            ],
            'internal with error' => [
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
            ],
            'external with change made by' => [
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
            ],
            'internal with change made by' => [
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
            ],
        ];
    }
}
