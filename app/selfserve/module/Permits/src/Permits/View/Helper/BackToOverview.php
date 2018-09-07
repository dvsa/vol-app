<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Link back to the permits overview page for that id
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BackToOverview extends AbstractHelper
{
    const BACK_LINK_LABEL = 'common.link.back.label';

    private $linkTemplate = '<a href="%s" class="%s">%s</a>';

    /**
     * Return a back link
     *
     * @param string|null $label Parameters
     *
     * @return string
     */
    public function __invoke(?string $label = 'common.link.back.label'): string
    {
        /**
         * @todo temporary to stop "return to overview" links losing their styling - can be removed following olcs-21034
         */
        $linkClass = (self::BACK_LINK_LABEL === $label ? 'back-link' : 'ecmt-overview-return-link');

        $label = $this->view->escapeHtml($this->view->translate($label));
        $url = $this->view->url('permits/application-overview', [], [], true);
        return sprintf($this->linkTemplate, $url, $linkClass, $label);
    }
}
