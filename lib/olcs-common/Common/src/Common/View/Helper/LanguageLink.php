<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Common\Preference\Language;

class LanguageLink extends AbstractHelper
{
    public function __construct(private readonly Language $languagePref)
    {
    }

    public function __invoke(): string
    {
        if ($this->languagePref->getPreference() === Language::OPTION_CY) {
            return '<a class="govuk-footer__link" href="?lang=en" hreflang="en">English</a>';
        }
        return '<a class="govuk-footer__link" href="?lang=cy" hreflang="cy">Cymraeg</a>';
    }
}
