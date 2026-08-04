<?php

namespace Common\View\Helper;

use HTMLPurifier;
use Laminas\View\Helper\AbstractHelper;

/**
 * EscapeHtml with whitelisted tags
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class EscapeHtml extends AbstractHelper
{
    /**
     * EscapeHtml constructor.
     *
     * @return void
     */
    public function __construct(
        /** @var HtmlPurifier $htmlPurifierService */
        private HtmlPurifier $htmlPurifierService
    ) {
    }

    /**
     * @param string $toEscape
     */
    public function __invoke($toEscape): string
    {
        if (is_null($toEscape)) {
            return '';
        }

        return $this->htmlPurifierService->purify($toEscape);
    }
}
