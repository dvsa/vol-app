<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\LicenceStatusSelfserve;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LicenceStatusSelfserveTest extends MockeryTestCase
{
    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new LicenceStatusSelfserve($this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test format
     *
     * @dataProvider provider
     * @param array $data
     * @param string $expected
     */
    public function testFormat($data, $expected): void
    {
        $this->translator->shouldReceive('translate')->andReturnUsing(
            static fn($message) => 'TRANSLATED_' . $message
        );

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'Valid' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_VALID,
                        'description' => 'Valid'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--green">TRANSLATED_Valid</span>',
            ],
            'Suspended' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_SUSPENDED,
                        'description' => 'Suspended'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--orange">TRANSLATED_Suspended</span>',
            ],
            'Curtailed' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_CURTAILED,
                        'description' => 'Curtailed'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--orange">TRANSLATED_Curtailed</span>',
            ],
            'Under consideration' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_UNDER_CONSIDERATION,
                        'description' => 'Under consideration'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--orange">TRANSLATED_Under consideration</span>',
            ],
            'Granted' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_GRANTED,
                        'description' => 'Granted'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--orange">TRANSLATED_Granted</span>',
            ],
            'Surrender under consideration' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                        'description' => 'Surrender under consideration'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--green">TRANSLATED_Surrender under consideration</span>',
            ],
            'Surrendered' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_SURRENDERED,
                        'description' => 'Surrendered'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_Surrendered</span>',
            ],
            'Revoked' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_REVOKED,
                        'description' => 'Revoked'
                    ],
                    'licNo' => 'OB123',
                    'id' => 2
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_Revoked</span>',
            ],
            'Terminated' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_TERMINATED,
                        'description' => 'Terminated'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_Terminated</span>',
            ],
            'CNS' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
                        'description' => 'CNS'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_CNS</span>',
            ],
            'Withdrawn' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_WITHDRAWN,
                        'description' => 'Withdrawn'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_Withdrawn</span>',
            ],
            'Refused' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_REFUSED,
                        'description' => 'Refused'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_Refused</span>',
            ],
            'Not taken up' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_NOT_TAKEN_UP,
                        'description' => 'Not taken up'
                    ],
                    'licNo' => 'OB123',
                    'id' => 2
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_Not taken up</span>',
            ],
            'Cancelled' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_CANCELLED,
                        'description' => 'Cancelled'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--grey">TRANSLATED_Cancelled</span>',
            ],
            'Unknown' => [
                [
                    'status' => [
                        'id' => 'unknown',
                        'description' => 'Unknown'
                    ],
                ],
                '<span class="govuk-tag govuk-tag--grey">TRANSLATED_Unknown</span>',
            ],
            'Expired' => [
                [
                    'status' => [
                        'id' => 'unknown',
                        'description' => 'Unknown'
                    ],
                    'isExpired' => true,
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_licence.status.expired</span>',
            ],
            'Expiring' => [
                [
                    'status' => [
                        'id' => 'unknown',
                        'description' => 'Unknown'
                    ],
                    'isExpiring' => true,
                ],
                '<span class="govuk-tag govuk-tag--red">TRANSLATED_licence.status.expiring</span>',
            ],
            'Expiring but Surrendered' => [
                [
                    'status' => [
                        'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                        'description' => 'Surrender under consideration'
                    ],
                    'isExpiring' => true,
                ],
                '<span class="govuk-tag govuk-tag--green">TRANSLATED_Surrender under consideration</span>',
            ],
        ];
    }
}
