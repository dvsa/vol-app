<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\Preference\Language;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\LanguageNavigation;
use PHPUnit\Framework\Attributes\DataProvider;

final class LanguageNavigationTest extends MockeryTestCase
{
    private const string ENGLISH_CURRENT = '<nav class="govuk-language-navigation" aria-label="Language">' . "\n" .
        '  <ul class="govuk-language-navigation__list">' . "\n" .
        '    <li class="govuk-language-navigation__list-item">' . "\n" .
        '      <span class="govuk-language-navigation__text" aria-current="true" lang="en">English</span>' . "\n" .
        '    </li>' . "\n" .
        '    <li class="govuk-language-navigation__list-item">' . "\n" .
        '      <a class="govuk-language-navigation__link" href="?lang=cy" rel="alternate" lang="cy" hreflang="cy">' .
        'Cymraeg</a>' . "\n" .
        '    </li>' . "\n" .
        '  </ul>' . "\n" .
        '</nav>';

    private const string WELSH_CURRENT = '<nav class="govuk-language-navigation" aria-label="Dewis iaith">' . "\n" .
        '  <ul class="govuk-language-navigation__list">' . "\n" .
        '    <li class="govuk-language-navigation__list-item">' . "\n" .
        '      <a class="govuk-language-navigation__link" href="?lang=en" rel="alternate" lang="en" hreflang="en">' .
        'English</a>' . "\n" .
        '    </li>' . "\n" .
        '    <li class="govuk-language-navigation__list-item">' . "\n" .
        '      <span class="govuk-language-navigation__text" aria-current="true" lang="cy">Cymraeg</span>' . "\n" .
        '    </li>' . "\n" .
        '  </ul>' . "\n" .
        '</nav>';

    #[DataProvider('dpInvoke')]
    public function testInvoke(mixed $preference, string $expected): void
    {
        $languagePref = m::mock(Language::class);
        $languagePref->expects('getPreference')->withNoArgs()->andReturn($preference);

        $this->assertSame($expected, (new LanguageNavigation($languagePref))->__invoke());
    }

    /**
     * @return \Iterator<string, array{mixed, string}>
     */
    public static function dpInvoke(): \Iterator
    {
        yield 'English' => [Language::OPTION_EN, self::ENGLISH_CURRENT];
        yield 'Welsh' => [Language::OPTION_CY, self::WELSH_CURRENT];
        // the preference comes from an unvalidated cookie, so anything else is treated as English
        yield 'unsupported' => ['fr', self::ENGLISH_CURRENT];
    }
}
