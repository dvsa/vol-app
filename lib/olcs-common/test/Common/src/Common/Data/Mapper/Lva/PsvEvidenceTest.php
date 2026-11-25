<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\PsvLargeEvidence;
use Common\Data\Mapper\Lva\PsvSmallEvidence;
use Common\RefData;

#[\PHPUnit\Framework\Attributes\CoversClass(PsvSmallEvidence::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(PsvLargeEvidence::class)]
final class PsvEvidenceTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('storedEvidenceProvider')]
    public function testSmallVehicleEvidenceMapsStoredChoice($storedValue, array $expectedEvidence): void
    {
        $result = PsvSmallEvidence::mapFromResult([
            'id' => 1,
            'version' => 2,
            'smallVehicleEvidenceUploaded' => $storedValue,
        ]);

        $this->assertSame($expectedEvidence, $result['evidence']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('storedEvidenceProvider')]
    public function testLargeVehicleEvidenceMapsStoredChoice($storedValue, array $expectedEvidence): void
    {
        $result = PsvLargeEvidence::mapFromResult([
            'id' => 1,
            'version' => 2,
            'occupationEvidenceUploaded' => $storedValue,
        ]);

        $this->assertSame($expectedEvidence, $result['evidence']);
    }

    public static function storedEvidenceProvider(): array
    {
        return [
            'upload now integer' => [
                RefData::AD_UPLOAD_NOW,
                ['uploadNowRadio' => RefData::AD_UPLOAD_NOW],
            ],
            'upload now database string' => [
                (string) RefData::AD_UPLOAD_NOW,
                ['uploadNowRadio' => RefData::AD_UPLOAD_NOW],
            ],
            'upload later integer' => [
                RefData::AD_UPLOAD_LATER,
                ['uploadNowRadio' => RefData::AD_UPLOAD_LATER],
            ],
            'upload later database string' => [
                (string) RefData::AD_UPLOAD_LATER,
                ['uploadNowRadio' => RefData::AD_UPLOAD_LATER],
            ],
            'no saved choice' => [
                null,
                ['uploadNowRadio' => null],
            ],
            'legacy post value' => [
                RefData::AD_POST,
                ['uploadNowRadio' => null],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('postedEvidenceProvider')]
    public function testSmallVehicleEvidenceMapsPostedChoice($postedValue, ?int $expectedChoice): void
    {
        $result = PsvSmallEvidence::mapFromPost([
            'evidence' => [
                'uploadNow' => $postedValue,
                'files' => ['list' => ['document']],
            ],
        ]);

        $this->assertSame($expectedChoice, $result['evidence']['uploadNowRadio']);
        $this->assertSame(1, $result['evidence']['uploadedFileCount']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('postedEvidenceProvider')]
    public function testLargeVehicleEvidenceMapsPostedChoice($postedValue, ?int $expectedChoice): void
    {
        $result = PsvLargeEvidence::mapFromPost([
            'evidence' => ['uploadNow' => $postedValue],
        ]);

        $this->assertSame($expectedChoice, $result['evidence']['uploadNowRadio']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('savedChoiceProvider')]
    public function testSmallVehicleEvidenceMapsCombinedFormChoice($choice, ?int $expectedValue): void
    {
        $result = PsvSmallEvidence::mapFromForm([
            'id' => 1,
            'version' => 2,
            'evidence' => ['uploadNowRadio' => $choice],
        ]);

        $this->assertSame($expectedValue, $result['evidenceUploadType']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('savedChoiceProvider')]
    public function testLargeVehicleEvidenceMapsCombinedFormChoice($choice, ?int $expectedValue): void
    {
        $result = PsvLargeEvidence::mapFromForm([
            'id' => 1,
            'version' => 2,
            'evidence' => ['uploadNowRadio' => $choice],
        ]);

        $this->assertSame($expectedValue, $result['evidenceUploadType']);
    }

    public static function savedChoiceProvider(): array
    {
        return [
            'upload now' => [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_NOW],
            'upload later' => [RefData::AD_UPLOAD_LATER, RefData::AD_UPLOAD_LATER],
            'no choice' => [null, null],
        ];
    }

    public static function postedEvidenceProvider(): array
    {
        return [
            'upload now' => [RefData::AD_UPLOAD_NOW, RefData::AD_UPLOAD_NOW],
            'upload later' => [RefData::AD_UPLOAD_LATER, RefData::AD_UPLOAD_LATER],
            'no choice' => [null, null],
        ];
    }
}
