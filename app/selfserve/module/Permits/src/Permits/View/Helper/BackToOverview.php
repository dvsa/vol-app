<?php

namespace Permits\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Link back to the permits overview page for that id
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BackToOverview extends AbstractHelper
{
    const BACK_LINK_LABELS = [
        'common.link.back.label',
        'common.link.back-to-permits.label',
    ];

    private $linkTemplate = '<a href="%s" class="%s">%s</a>';

    /**
     * Return a back link
     *
     * @param string|null $label Parameters
     * @param string|null $uri
     * @param array|null $params
     *
     * @return string
     */
    public function __invoke(
        ?string $label = 'common.link.back.label',
        ?string $uri = '',
        ?array $params = [],
        ?array $options = []
    ): string {
        /**
         * @todo temporary to stop "return to overview" links losing their styling - can be removed following olcs-21034
         */
        $linkClass = 'govuk-link govuk-!-margin-bottom-7';
        if (in_array($label, self::BACK_LINK_LABELS)) {
            $linkClass = 'govuk-back-link govuk-!-margin-bottom-7';
        }

        $label = $this->view->escapeHtml($this->view->translate($label));
        $url = $this->view->url(
            empty($uri) ? IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW : $uri,
            $params ?? [],
            $options ?? [],
            true
        );

        return sprintf($this->linkTemplate, $url, $linkClass, $label);
    }
}
