<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Produces a standard modal link, mostly found on internal pages but occasionally on selfserve
 * Calls the standard link helper with an additional class that can be picked up by the JS code
 *
 * @see Link
 */
class LinkModal extends AbstractHelper
{
    public const EXTRA_MODAL_CLASS = 'js-modal-ajax';

    public function __invoke(string $url, string $linkText, string $class = 'govuk-link'): string
    {
        $linkClass = $class . ' ' . self::EXTRA_MODAL_CLASS;
        return $this->view->link($url, $linkText, $linkClass);
    }
}
