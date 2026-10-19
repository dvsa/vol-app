<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Common\Preference\Language;

/**
 * GOV.UK Frontend language navigation, for the end slot of the service navigation
 */
class LanguageNavigation extends AbstractHelper
{
    public const string HTML_NAV = '<nav class="govuk-language-navigation" aria-label="%s">
  <ul class="govuk-language-navigation__list">
%s  </ul>
</nav>';
    public const string HTML_CURRENT = '    <li class="govuk-language-navigation__list-item">
      <span class="govuk-language-navigation__text" aria-current="true" lang="%1$s">%2$s</span>
    </li>
';
    public const string HTML_LINK = '    <li class="govuk-language-navigation__list-item">
      <a class="govuk-language-navigation__link" href="?lang=%1$s" rel="alternate" lang="%1$s" hreflang="%1$s">%2$s</a>
    </li>
';

    /** Each language's name, written in that language */
    private const array NAMES = [
        Language::OPTION_EN => 'English',
        Language::OPTION_CY => 'Cymraeg',
    ];

    /** The landmark label, written in the language of the current page */
    private const array LABELS = [
        Language::OPTION_EN => 'Language',
        Language::OPTION_CY => 'Dewis iaith',
    ];

    public function __construct(private readonly Language $languagePref)
    {
    }

    public function __invoke(): string
    {
        $current = $this->languagePref->getPreference() === Language::OPTION_CY
            ? Language::OPTION_CY
            : Language::OPTION_EN;

        $items = '';
        foreach (self::NAMES as $lang => $name) {
            $items .= sprintf($lang === $current ? self::HTML_CURRENT : self::HTML_LINK, $lang, $name);
        }

        return sprintf(self::HTML_NAV, self::LABELS[$current], $items);
    }
}
