<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Produces a standard link with all necessary escaping
 * defaults to a govuk-link css class
 * provides a default "opens in new tab" message for screen readers
 * external links add target="_blank" and rel="external noreferrer noopener"
 * "noreferrer noopener" prevents reverse tabnabbing attacks
 *
 * @see https://design-system.service.gov.uk/styles/typography/#links
 * @see https://owasp.org/www-community/attacks/Reverse_Tabnabbing
 */
class LinkNewWindow extends AbstractHelper
{
    public const NEW_TAB_MESSAGE = 'link.opens-new-window';

    public const HIDDEN_NEW_TAB_SPAN = '<span class="govuk-visually-hidden">%s</span>';

    public const LINK_FORMAT = '<a href="%s" class="%s" target="_blank">%s%s</a>';

    public const LINK_FORMAT_EXTERNAL = '<a href="%s" class="%s" target="_blank" rel="external noreferrer noopener">%s%s</a>';

    public function __invoke(
        string $url,
        string $linkText,
        string $class = 'govuk-link',
        bool $hideNewTabMessage = false,
        bool $isExternal = false
    ): string {
        //we have the option to hide the new tab message, this is defaulted to off
        $hiddenNewTabMarkup = '';

        //the new tab text is the same, regardless of whether it is hidden
        $escapedNewTabText = $this->view->escapeHtml($this->view->translate(self::NEW_TAB_MESSAGE));

        //the link text is escaped first - the new tab text may be appended to this depending on the option selected
        $escapedText = $this->view->escapeHtml($this->view->translate($linkText));

        //decide whether the new tab text goes in the hidden span or is appended to the link text itself
        if ($hideNewTabMessage) {
            $hiddenNewTabMarkup = sprintf(self::HIDDEN_NEW_TAB_SPAN, $escapedNewTabText);
        } else {
            $escapedText = $escapedText . ' ' . $escapedNewTabText;
        }

        $linkFormat = self::LINK_FORMAT;

        if ($isExternal) {
            $linkFormat = self::LINK_FORMAT_EXTERNAL;
        }

        $escapedUrl = $this->view->escapeHtmlAttr($url);
        $escapedClass = $this->view->escapeHtmlAttr($class);

        return sprintf($linkFormat, $escapedUrl, $escapedClass, $escapedText, $hiddenNewTabMarkup);
    }
}
