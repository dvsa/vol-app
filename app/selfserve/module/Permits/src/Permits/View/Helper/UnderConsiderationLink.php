<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Generate link from the change answer page, back to the correct page
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UnderConsiderationLink extends AbstractHelper
{
    private $linkTemplate = '<a href="%s" class="back-link">%s</a>';

    /**
     * Link to move from the change answer page to the answer being changed
     *
     * @param string      $route Route
     * @param string|null $label translated label
     *
     * @return string
     */
    public function __invoke(string $route, ?string $label = 'common.link.back.label'): string
    {
        $label = $this->view->escapeHtml($this->view->translate($label));
        $url = $this->view->url('permits/' . $route, [], [], true);
        return sprintf($this->linkTemplate, $url, $label);
    }
}
