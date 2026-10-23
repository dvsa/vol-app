<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Common\Service\Helper\TranslationHelperService;
use Laminas\View\Helper\AbstractHelper;

/**
 * Visually hidden "Important:" for text marked by a warning or information icon drawn in CSS, so
 * screen reader users know the message is important
 */
class ImportantPrefix extends AbstractHelper
{
    public const string HTML = '<span class="govuk-visually-hidden">%s: </span>';

    public function __construct(private readonly TranslationHelperService $translator)
    {
    }

    public function __invoke(): string
    {
        return sprintf(self::HTML, htmlspecialchars($this->translator->translate('lva-summary-important')));
    }
}
