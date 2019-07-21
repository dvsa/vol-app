<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Generate a link within a HTML anchor tag, and include a context value for accessibility
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Link extends AbstractHelper
{
    const LINK_TEMPLATE = '<a class="%s" href="%s">%s<span class="govuk-visually-hidden">%s</span></a>';

    /**
     * Generate a link with optional context for accessibility
     *
     * @param string $route       the route
     * @param string $label       link anchor text
     * @param string $context     context (for accessibility)
     * @param array  $params      params
     * @param array  $options     options
     * @param bool   $reUseParams whether to reuse matched parameters
     * @param string $linkClass   the css class of the link
     *
     * @return string
     */
    public function __invoke(
        string $route,
        string $label,
        string $context = '',
        array $params = [],
        array $options = [],
        bool $reUseParams = true,
        string $linkClass = 'govuk-link'
    ): string {
        $label = $this->view->escapeHtml($this->view->translate($label));
        $context = $this->view->escapeHtml($this->view->translate($context));
        $url = $this->view->url($route, $params, $options, $reUseParams);
        return sprintf(self::LINK_TEMPLATE, $linkClass, $url, $label, $context);
    }
}
