<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp;

use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** Covers removing a caseworker's entry; laying entries over a result is covered through the handlers. */
#[CoversClass(AnalysisAnnotationOverlay::class)]
final class AnalysisAnnotationOverlayTest extends TestCase
{
    private const array ANNOTATIONS = ['rows' => [
        'name' => ['flag' => 'fail', 'comment' => 'x'],
        'largeDeposit' => ['flag' => 'pass', 'comment' => 'y'],
    ]];

    public function testHasRowSaysWhetherACheckHasACaseworkerEntry(): void
    {
        $sut = new AnalysisAnnotationOverlay();

        $this->assertTrue($sut->hasRow(self::ANNOTATIONS, 'name'));
        $this->assertFalse($sut->hasRow(self::ANNOTATIONS, 'authenticity'));
        $this->assertFalse($sut->hasRow(null, 'name'));
        $this->assertFalse($sut->hasRow(['rows' => 'not an array'], 'name'));
    }

    /** Taking a check's entry away puts it back exactly as the analyser left it; the others stay. */
    public function testWithoutRowRemovesOnlyThatChecksEntry(): void
    {
        $sut = new AnalysisAnnotationOverlay();

        $updated = $sut->withoutRow(self::ANNOTATIONS, 'name');

        $this->assertSame(['rows' => ['largeDeposit' => ['flag' => 'pass', 'comment' => 'y']]], $updated);
        $this->assertSame(self::ANNOTATIONS, $sut->withoutRow(self::ANNOTATIONS, 'authenticity'));
        $this->assertSame(['rows' => []], $sut->withoutRow(null, 'name'));
    }
}
