<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\FinancialEvidence;
use Common\RefData;

/**
 * Financial Evidence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class FinancialEvidenceTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultProvider')]
    public function testMapFromResult($input, $expected): void
    {
        $this->assertEquals($expected, FinancialEvidence::mapFromResult($input));
    }

    /**
     * @return \Iterator<(int | string), array<array<(array<(int | null)> | int | null)>>>
     *
     * @psalm-return list{list{array{financialEvidenceUploaded: 1, id: 1, version: 2}, array{id: 1, version: 2, evidence: array{uploadNowRadio: 1, uploadLaterRadio: null, sendByPostRadio: null}}}, list{array{financialEvidenceUploaded: 2, id: 1, version: 2}, array{id: 1, version: 2, evidence: array{uploadNowRadio: null, uploadLaterRadio: 2, sendByPostRadio: null}}}, list{array{financialEvidenceUploaded: 0, id: 1, version: 2}, array{id: 1, version: 2, evidence: array{uploadNowRadio: null, uploadLaterRadio: null, sendByPostRadio: 0}}}, list{array{financialEvidenceUploaded: null, id: 1, version: 2}, array{id: 1, version: 2, evidence: array{uploadNowRadio: 1, uploadLaterRadio: null, sendByPostRadio: null}}}}
     */
    public static function mapFromResultProvider(): \Iterator
    {
        yield [
            [
                'financialEvidenceUploaded' => RefData::AD_UPLOAD_NOW,
                'id' => 1,
                'version' => 2
            ],
            [
                'id'       => 1,
                'version'  => 2,
                'evidence' => [
                    'uploadNowRadio' => RefData::AD_UPLOAD_NOW,
                    'uploadLaterRadio' => null,
                ]
            ]
        ];
        yield [
            [
                'financialEvidenceUploaded' => RefData::AD_UPLOAD_LATER,
                'id' => 1,
                'version' => 2
            ],
            [
                'id'       => 1,
                'version'  => 2,
                'evidence' => [
                    'uploadNowRadio' => null,
                    'uploadLaterRadio' => RefData::AD_UPLOAD_LATER,
                ]
            ]
        ];
        yield [
            [
                'financialEvidenceUploaded' => null,
                'id' => 1,
                'version' => 2
            ],
            [
                'id'       => 1,
                'version'  => 2,
                'evidence' => [
                    'uploadNowRadio' => RefData::AD_UPLOAD_NOW,
                    'uploadLaterRadio' => null,
                ]
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromPostProvider')]
    public function testMapFromPost($input, $expected): void
    {
        $this->assertEquals($expected, FinancialEvidence::mapFromPost($input));
    }

    /**
     * @return \Iterator<(int | string), array<array<array<(array<array<string>> | int | string | null)>>>>
     *
     * @psalm-return list{list{array{evidence: array{uploadNow: 1, files: array{list: list{'foo'}}, bar: 'cake'}}, array{evidence: array{uploadNowRadio: 1, uploadLaterRadio: null, sendByPostRadio: null, uploadedFileCount: 1, uploadNow: 1, files: array{list: list{'foo'}}, bar: 'cake'}}}, list{array{evidence: array{uploadNow: 2, files: array{list: list{'foo'}}, bar: 'cake'}}, array{evidence: array{uploadNowRadio: null, uploadLaterRadio: 2, sendByPostRadio: null, uploadedFileCount: 1, uploadNow: 2, files: array{list: list{'foo'}}, bar: 'cake'}}}, list{array{evidence: array{uploadNow: 0, files: array{list: list{'foo'}}, bar: 'cake'}}, array{evidence: array{uploadNowRadio: null, uploadLaterRadio: null, sendByPostRadio: 0, uploadedFileCount: 1, uploadNow: 0, files: array{list: list{'foo'}}, bar: 'cake'}}}, list{array<never, never>, array<never, never>}}
     */
    public static function mapFromPostProvider(): \Iterator
    {
        yield [
            [
                'evidence' => [
                    'uploadNow' => RefData::AD_UPLOAD_NOW,
                    'files' => [
                        'list' => [
                            'foo'
                        ]
                    ],
                    'bar' => 'cake'
                ]
            ],
            [
                'evidence' => [
                    'uploadNowRadio' => RefData::AD_UPLOAD_NOW,
                    'uploadLaterRadio' => null,
                    'uploadedFileCount' => 1,
                    'uploadNow' => RefData::AD_UPLOAD_NOW,
                    'files' => [
                        'list' => [
                            'foo'
                        ]
                    ],
                    'bar' => 'cake'
                ]
            ]
        ];
        yield [
            [
                'evidence' => [
                    'uploadNow' => RefData::AD_UPLOAD_LATER,
                    'files' => [
                        'list' => [
                            'foo'
                        ]
                    ],
                    'bar' => 'cake'
                ]
            ],
            [
                'evidence' => [
                    'uploadNowRadio' => null,
                    'uploadLaterRadio' => RefData::AD_UPLOAD_LATER,
                    'uploadedFileCount' => 1,
                    'uploadNow' => RefData::AD_UPLOAD_LATER,
                    'files' => [
                        'list' => [
                            'foo'
                        ]
                    ],
                    'bar' => 'cake'
                ]
            ]
        ];
        yield [
            [],
            []
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormProvider')]
    public function testMapFromForm($input, $expected): void
    {
        $this->assertEquals($expected, FinancialEvidence::mapFromForm($input));
    }

    /**
     * @return \Iterator<(int | string), array<array<(array<(int | null)> | int)>>>
     *
     * @psalm-return list{list{array{id: 1, version: 2, evidence: array{uploadNowRadio: 1, uploadLaterRadio: null, sendByPost: null}}, array{id: 1, version: 2, financialEvidenceUploaded: 1}}, list{array{id: 1, version: 2, evidence: array{uploadNowRadio: null, uploadLaterRadio: 2, sendByPost: null}}, array{id: 1, version: 2, financialEvidenceUploaded: 2}}, list{array{id: 1, version: 2, evidence: array{uploadNowRadio: null, uploadLaterRadio: null, sendByPost: 0}}, array{id: 1, version: 2, financialEvidenceUploaded: 0}}}
     */
    public static function mapFromFormProvider(): \Iterator
    {
        yield [
            [
                'id' => 1,
                'version' => 2,
                'evidence' => [
                    'uploadNowRadio' => RefData::AD_UPLOAD_NOW,
                    'uploadLaterRadio' => null,
                    'sendByPost' => null
                ]
            ],
            [
                'id' => 1,
                'version' => 2,
                'financialEvidenceUploaded' => RefData::AD_UPLOAD_NOW
            ]
        ];
        yield [
            [
                'id' => 1,
                'version' => 2,
                'evidence' => [
                    'uploadNowRadio' => null,
                    'uploadLaterRadio' => RefData::AD_UPLOAD_LATER,
                    'sendByPost' => null
                ]
            ],
            [
                'id' => 1,
                'version' => 2,
                'financialEvidenceUploaded' => RefData::AD_UPLOAD_LATER
            ]
        ];
        yield [
            [
                'id' => 1,
                'version' => 2,
                'evidence' => [
                    'uploadNowRadio' => null,
                    'uploadLaterRadio' => null,
                    'sendByPost' => RefData::AD_POST
                ]
            ],
            [
                'id' => 1,
                'version' => 2,
                'financialEvidenceUploaded' => RefData::AD_POST
            ]
        ];
    }
}
