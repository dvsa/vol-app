<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data\Letter;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\Letter\LetterAppendix;

#[\PHPUnit\Framework\Attributes\CoversClass(LetterAppendix::class)]
final class LetterAppendixTest extends AbstractListDataServiceTestCase
{
    private LetterAppendix $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new LetterAppendix($this->abstractListDataServiceServices);
    }

    /**
     * VOL-7103: name and appendixType live on the versioned record (currentVersion),
     * not at the top level, so the multi-select must read them from there.
     */
    public function testFormatDataReadsNameAndTypeFromCurrentVersion(): void
    {
        $source = [
            [
                'id' => 5,
                'currentVersion' => ['name' => 'Interim guidance', 'appendixType' => 'editable'],
            ],
            [
                'id' => 8,
                'currentVersion' => ['name' => 'Fee schedule', 'appendixType' => 'pdf'],
            ],
        ];

        $expected = [
            5 => 'Interim guidance (Editable)',
            8 => 'Fee schedule (Pdf)',
        ];

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    public function testFormatDataFallsBackToTopLevelThenId(): void
    {
        $source = [
            // legacy/top-level shape still honoured
            ['id' => 1, 'name' => 'Top level name', 'appendixType' => 'pdf'],
            // nothing usable -> id placeholder, no type suffix
            ['id' => 2],
        ];

        $expected = [
            1 => 'Top level name (Pdf)',
            2 => 'Appendix #2',
        ];

        $this->assertEquals($expected, $this->sut->formatData($source));
    }
}
