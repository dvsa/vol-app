<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Document;

use Olcs\Controller\Document\NewLetterTemplateOption;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(NewLetterTemplateOption::class)]
final class NewLetterTemplateOptionTest extends TestCase
{
    public function testValueForPrefixesTheTemplateId(): void
    {
        $this->assertSame('new-7', NewLetterTemplateOption::valueFor(7));
    }

    public function testMatchesOnlyNewLetterValues(): void
    {
        $this->assertTrue(NewLetterTemplateOption::matches('new-7'));
        $this->assertFalse(NewLetterTemplateOption::matches('7'));
        $this->assertFalse(NewLetterTemplateOption::matches(7));
        $this->assertFalse(NewLetterTemplateOption::matches(null));
    }

    public function testTemplateIdReadsEitherForm(): void
    {
        $this->assertSame(7, NewLetterTemplateOption::templateId('new-7'));
        $this->assertSame(7, NewLetterTemplateOption::templateId('7'));
        $this->assertSame(7, NewLetterTemplateOption::templateId(7));
    }
}
