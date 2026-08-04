<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Produces a standard link to external sites opening in a new window
 * Works by calling the LinkNewWindow helper with the external option set to true
 *
 * @see LinkNewWindow
 */
class LinkNewWindowExternal extends AbstractHelper
{
    public function __invoke(
        string $url,
        string $linkText,
        string $class = 'govuk-link',
        bool $hideNewTabMessage = false
    ): string {
        return $this->view->linkNewWindow($url, $linkText, $class, $hideNewTabMessage, true);
    }
}
