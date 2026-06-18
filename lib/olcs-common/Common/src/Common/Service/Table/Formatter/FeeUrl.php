<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * Fee URL formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeUrl implements FormatterPluginManagerInterface
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
        $query      = $this->request->getQuery()->toArray();

        // OLCS-24863 - the code below is because of the changes introduced by OLCS-23728
        // where some routing was changed to '.../table', so the following code "correct" it
        switch ($matchedRouteName) {
            case 'licence/irhp-application-fees/table':
                $matchedRouteName = 'licence/irhp-application-fees';
                break;
            case 'licence/irhp-fees/table':
                $matchedRouteName = 'licence/irhp-fees';
                break;
        }

        $url = match ($matchedRouteName) {
            'operator/fees', 'licence/bus-fees', 'licence/fees', 'licence/irhp-fees', 'licence/irhp-application-fees', 'lva-application/fees' => $this->urlHelper->fromRoute(
                $matchedRouteName . '/fee_action',
                ['fee' => $row['id'], 'action' => 'edit-fee'],
                ['query' => $query],
                true
            ),
            'fees' => $this->urlHelper->fromRoute('fees/pay', ['fee' => $row['id']], ['query' => $query], true),
            default => $this->urlHelper->fromRoute(
                'admin-dashboard/admin-payment-processing/misc-fees/fee_action',
                ['fee' => $row['id'], 'action' => 'edit-fee', 'controller' => 'Admin\PaymentProcessingController'],
                ['query' => $query],
                true
            ),
        };

        return '<a class="govuk-link" href="' . $url . '">' . $row['description'] . '</a>';
    }
}
