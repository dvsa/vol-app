<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationFinancialEvidenceReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Financial Evidence Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFinancialEvidenceReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    /** @var QueryHandlerManager */
    protected $qhManager;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->qhManager = m::mock(QueryHandlerManager::class);

        $this->sut = new ApplicationFinancialEvidenceReviewService(
            $abstractReviewServiceServices,
            $this->qhManager
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('uploadProvider')]
    public function testGetConfigFromDataWithoutDocs(mixed $uploaded, mixed $reviewText): void
    {
        $data = [
            'id' => 123,
            'financialEvidenceUploaded' => $uploaded
        ];

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-financial-evidence-no-of-vehicles',
                        'value' => 6
                    ],
                    [
                        'label' => 'application-review-financial-evidence-required-finance',
                        'value' => '£123,456'
                    ],
                    [
                        'label' => 'application-review-financial-evidence-evidence',
                        'noEscape' => true,
                        'value' => $reviewText
                    ]
                ]
            ]
        ];

        $this->qhManager->shouldReceive('handleQuery->serialize')
            ->andReturn(
                [
                    'financialEvidence' => [
                        'requiredFinance' => 123456,
                        'applicationVehicles' => 1,
                        'otherLicenceVehicles' => 2,
                        'otherApplicationVehicles' => 3
                    ],
                ]
            );

        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function uploadProvider(): array
    {
        return [
            [
                Application::FINANCIAL_EVIDENCE_SEND_IN_POST,
                'application-review-financial-evidence-evidence-post-translated'
            ],
            [
                Application::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                'application-review-financial-evidence-evidence-later-translated'
            ]
        ];
    }

    public function testGetConfigFromDataWithDocs(): void
    {
        $data = [
            'id' => 123,
            'financialEvidenceUploaded' => Application::FINANCIAL_EVIDENCE_UPLOADED
        ];

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-financial-evidence-no-of-vehicles',
                        'value' => 6
                    ],
                    [
                        'label' => 'application-review-financial-evidence-required-finance',
                        'value' => '£123,456'
                    ],
                    [
                        'label' => 'application-review-financial-evidence-evidence',
                        'noEscape' => true,
                        'value' => 'foo.txt<br>bar.txt'
                    ]
                ]
            ]
        ];

        $document1 = [
            'description' => 'foo.txt'
        ];
        $document2 = [
            'description' => 'bar.txt'
        ];

        $this->qhManager->shouldReceive('handleQuery->serialize')
            ->andReturn(
                [
                    'financialEvidence' => [
                        'requiredFinance' => 123456,
                        'applicationVehicles' => 1,
                        'otherLicenceVehicles' => 2,
                        'otherApplicationVehicles' => 3
                    ],
                    'documents' => [
                        $document1,
                        $document2
                    ]
                ]
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
