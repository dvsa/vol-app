<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\Preference\Language;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\LanguageLink;

final class LanguageLinkTest extends MockeryTestCase
{
    private Language&m\MockInterface $languagePref;
    private LanguageLink $viewHelper;

    #[\Override]
    protected function setUp(): void
    {
        $languagePref = m::mock(Language::class);
        $this->languagePref = $languagePref;

        $this->viewHelper = new LanguageLink($languagePref);
    }

    public function testInvoke(): void
    {
        $this->languagePref->expects('getPreference')
            ->andReturn(Language::OPTION_CY);

        $this->assertEquals('<a class="govuk-footer__link" href="?lang=en" hreflang="en">English</a>', $this->viewHelper->__invoke());
    }

    public function testInvokeEnglish(): void
    {
        $this->languagePref->expects('getPreference')
            ->andReturn(Language::OPTION_EN);

        $this->assertEquals('<a class="govuk-footer__link" href="?lang=cy" hreflang="cy">Cymraeg</a>', $this->viewHelper->__invoke());
    }
}
