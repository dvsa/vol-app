<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * External fee url
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeUrlExternal extends FeeUrl
{
    public function __construct(TreeRouteStack $router, private Request $request, private UrlHelperService $urlHelper)
    {
        parent::__construct($router, $this->request, $this->urlHelper);
    }

    /**
     * Format a fee amount
     *
     * @param array $row    row
     * @param array $column column
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = [])
    {
        if (isset($row['isExpiredForLicence']) && $row['isExpiredForLicence']) {
            $query = $this->request->getQuery()->toArray();
            $url = $this->urlHelper->fromRoute('fees/late', ['fee' => $row['id']], ['query' => $query], true);
            return '<a class="govuk-link" href="' . $url . '">' . Escape::html($row['description']) . '</a>';
        }

        return parent::format($row, $column);
    }
}
