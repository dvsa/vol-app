<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Produces a standard link with all necessary escaping, defaults to a govuk-link css class
 */
class Link extends AbstractHelper
{
    public const LINK_FORMAT = '<a href="%s" class="%s">%s</a>';

    public function __invoke(string $url, string $linkText, string $class = 'govuk-link'): string
    {
        $escapedUrl = $this->view->escapeHtmlAttr($url);
        $escapedText = $this->view->escapeHtml($this->view->translate($linkText));
        $escapedClass = $this->view->escapeHtmlAttr($class);

        return sprintf(self::LINK_FORMAT, $escapedUrl, $escapedClass, $escapedText);
    }
}
