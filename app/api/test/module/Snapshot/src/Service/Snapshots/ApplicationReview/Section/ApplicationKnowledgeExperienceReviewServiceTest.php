<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationKnowledgeExperienceReviewService;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class ApplicationKnowledgeExperienceReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    /** @var QueryHandlerManager */
    protected $qhManager;

    #[\Override]
    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(
            AbstractReviewServiceServices::class
        );

        $abstractReviewServiceServices
            ->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->qhManager = m::mock(QueryHandlerManager::class);

        $this->sut = new ApplicationKnowledgeExperienceReviewService(
            $abstractReviewServiceServices,
            $this->qhManager
        );
    }

    public function testGetConfigFromDataWithOlatSelected(): void
    {
        $data = [
            'id' => 123,
        ];

        $this->qhManager
            ->shouldReceive('handleQuery->serialize')
            ->andReturn([
                'knowledgeExperienceOlat' => 'Y',
                'documents' => [],
            ]);

        $this->mockTranslator
            ->shouldReceive('translate')
            ->with('Yes')
            ->andReturn('Yes-translated');

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-knowledge-experience-olat',
                        'value' => 'Yes-translated',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->sut->getConfigFromData($data)
        );
    }

    public function testGetConfigFromDataWithEvidenceDocuments(): void
    {
        $data = [
            'id' => 123,
        ];

        $this->qhManager
            ->shouldReceive('handleQuery->serialize')
            ->andReturn([
                'knowledgeExperienceOlat' => 'N',
                'documents' => [
                    [
                        'description' => 'foo.txt',
                    ],
                    [
                        'description' => 'bar.txt',
                    ],
                    [
                        'description' => '<img src=x onerror=alert(1)>.pdf',
                    ],
                ],
            ]);

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-knowledge-experience-evidence',
                        'noEscape' => true,
                        'value' => 'foo.txt<br>bar.txt<br>&lt;img src=x onerror=alert(1)&gt;.pdf',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->sut->getConfigFromData($data)
        );
    }

    public function testGetConfigFromDataWithOlatSelectedAndEvidenceDocuments(): void
    {
        $data = [
            'id' => 123,
        ];

        $this->qhManager
            ->shouldReceive('handleQuery->serialize')
            ->andReturn([
                'knowledgeExperienceOlat' => 'Y',
                'documents' => [
                    [
                        'description' => 'foo.txt',
                    ],
                    [
                        'description' => 'bar.txt',
                    ],
                ],
            ]);

        $this->mockTranslator
            ->shouldReceive('translate')
            ->with('Yes')
            ->andReturn('Yes-translated');

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-knowledge-experience-evidence',
                        'noEscape' => true,
                        'value' => 'foo.txt<br>bar.txt',
                    ],
                    [
                        'label' => 'application-review-knowledge-experience-olat',
                        'value' => 'Yes-translated',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->sut->getConfigFromData($data)
        );
    }

    public function testGetConfigFromDataWithNothingSupplied(): void
    {
        $data = [
            'id' => 123,
        ];

        $this->qhManager
            ->shouldReceive('handleQuery->serialize')
            ->andReturn([
                'knowledgeExperienceOlat' => 'N',
                'documents' => [],
            ]);

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-knowledge-experience-evidence',
                        'noEscape' => true,
                        'value' => '',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->sut->getConfigFromData($data)
        );
    }
}
