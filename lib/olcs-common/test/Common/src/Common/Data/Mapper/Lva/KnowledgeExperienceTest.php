<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\KnowledgeExperience;
use Common\RefData;

#[\PHPUnit\Framework\Attributes\CoversClass(KnowledgeExperience::class)]
final class KnowledgeExperienceTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultProvider')]
    public function testMapFromResult(?int $evidenceUploaded, ?string $olat, array $expectedEvidence): void
    {
        $result = KnowledgeExperience::mapFromResult([
            'id' => 1,
            'version' => 2,
            'knowledgeExperienceEvidenceUploaded' => $evidenceUploaded,
            'knowledgeExperienceOlat' => $olat,
        ]);

        $this->assertSame(
            [
                'id' => 1,
                'version' => 2,
                'evidence' => $expectedEvidence,
                'knowledgeExperienceOlat' => $olat,
            ],
            $result
        );
    }

    public static function mapFromResultProvider(): \Iterator
    {
        // unlike the other evidence sections, nothing is preselected on a first visit
        yield 'not yet answered' => [
            null,
            null,
            ['uploadNowRadio' => null, 'uploadLaterRadio' => null],
        ];

        yield 'upload now' => [
            RefData::AD_UPLOAD_NOW,
            'N',
            ['uploadNowRadio' => RefData::AD_UPLOAD_NOW, 'uploadLaterRadio' => null],
        ];

        yield 'upload later' => [
            RefData::AD_UPLOAD_LATER,
            'N',
            ['uploadNowRadio' => null, 'uploadLaterRadio' => RefData::AD_UPLOAD_LATER],
        ];

        yield 'olat only' => [
            null,
            'Y',
            ['uploadNowRadio' => null, 'uploadLaterRadio' => null],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormProvider')]
    public function testMapFromForm(array $evidence, array $olat, ?int $expectedUploadType, string $expectedOlat): void
    {
        $result = KnowledgeExperience::mapFromForm(
            ['id' => 1, 'version' => 2, 'evidence' => $evidence] + $olat
        );

        $this->assertSame(
            [
                'id' => 1,
                'version' => 2,
                'evidenceUploadType' => $expectedUploadType,
                'knowledgeExperienceOlat' => $expectedOlat,
            ],
            $result
        );
    }

    public static function mapFromFormProvider(): \Iterator
    {
        yield 'nothing chosen, olat unticked' => [
            ['uploadNowRadio' => null, 'uploadLaterRadio' => null],
            [],
            null,
            'N',
        ];

        yield 'upload now' => [
            ['uploadNowRadio' => '1', 'uploadLaterRadio' => null],
            [],
            RefData::AD_UPLOAD_NOW,
            'N',
        ];

        yield 'upload later' => [
            ['uploadNowRadio' => null, 'uploadLaterRadio' => '2'],
            [],
            RefData::AD_UPLOAD_LATER,
            'N',
        ];

        yield 'olat ticked' => [
            ['uploadNowRadio' => null, 'uploadLaterRadio' => null],
            ['knowledgeExperienceOlat' => 'Y'],
            null,
            'Y',
        ];
    }
}
