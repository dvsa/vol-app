<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\ImportantPrefix;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(ImportantPrefix::class)]
final class ImportantPrefixTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $translator = m::mock(TranslationHelperService::class);
        $translator->expects('translate')->with('lva-summary-important')->andReturn('Pwysig & <b>');

        $this->assertSame(
            '<span class="govuk-visually-hidden">Pwysig &amp; &lt;b&gt;: </span>',
            (new ImportantPrefix($translator))->__invoke()
        );
    }
}
