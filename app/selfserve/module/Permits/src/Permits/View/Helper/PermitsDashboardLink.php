<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Link back to the permits overview page for that id
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class PermitsDashboardLink extends AbstractHelper
{
    private $linkTemplate = '<a href="%s" class="%s">%s</a>';

    /**
     * Return a cancel link
     *
     * @param string|null $label Parameters
     *
     * @return string
     */
    public function __invoke(?string $label = 'cancel.button'): string
    {
        $linkClass = 'return-overview';

        $label = $this->view->escapeHtml($this->view->translate($label));
        $url = $this->view->url('permits', [], [], true);
        return sprintf($this->linkTemplate, $url, $linkClass, $label);
    }
}
