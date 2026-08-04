<?php

/**
 * Fee URL formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * Fee URL formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeIdUrl implements FormatterPluginManagerInterface
{
    public function __construct(private TreeRouteStack $router, private Request $request, private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format a fee URL
     *
     * @param      array $row
     * @param      array $column
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = [])
    {
        $routeMatch = $this->router->match($this->request);
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        // currently this is only used on /transaction pages
        $route = substr($matchedRouteName, 0, strpos($matchedRouteName, '/transaction'));

        $url = $this->urlHelper->fromRoute(
            $route,
            ['fee' => $row['id'], 'action' => 'edit-fee'],
            ['query' => $this->request->getQuery()->toArray()],
            true
        );

        return '<a class="govuk-link" href="' . $url . '">' . $row['id'] . '</a>';
    }
}
