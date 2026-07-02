<?php

namespace CommonTest\View\Helper;

use Common\Preference\Language;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\LanguageLink;

class LanguageLinkTest extends MockeryTestCase
{
    public $languagePref;
    /**
     * @var LanguageLink
     */
    protected $viewHelper;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $languagePref = m::mock(Language::class);
        $this->languagePref = $languagePref;

        $this->viewHelper = new LanguageLink($languagePref);

        $this->sm = new ServiceManager();
    }

    public function testInvoke(): void
    {
        $this->languagePref->shouldReceive('getPreference')
            ->andReturn(Language::OPTION_CY);

        $this->assertEquals('<a class="govuk-footer__link" href="?lang=en">English</a>', $this->viewHelper->__invoke());
    }

    public function testInvokeEnglish(): void
    {
        $this->languagePref->shouldReceive('getPreference')
            ->andReturn(Language::OPTION_EN);

        $helper = $this->viewHelper;

        $this->assertEquals('<a class="govuk-footer__link" href="?lang=cy">Cymraeg</a>', $helper());
    }
}
