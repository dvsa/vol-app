<?php

/**
 * Transaction Fee Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

// need to alias as RefData exists in Formatter namespace
use Common\RefData as Ref;
use Common\Service\Helper\UrlHelperService;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * Transaction Fee Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionFeeStatus implements FormatterPluginManagerInterface
{
    public function __construct(private TreeRouteStack $router, private Request $request, private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format a transaction fee status
     *
     * @param      array $row
     * @param      array $column
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = [])
    {
        $status = 'Applied';

        if (isset($row['reversingTransaction'])) {
            $routeMatch = $this->router->match($this->request);
            $matchedRouteName = $routeMatch->getMatchedRouteName();
            $params = [
                'transaction' => $row['reversingTransaction']['id'],
                'action' => 'edit-fee',
            ];
            $url = $this->urlHelper->fromRoute($matchedRouteName, $params, [], true);

            $status = match ($row['reversingTransaction']['type']) {
                Ref::TRANSACTION_TYPE_REFUND => 'Refunded',
                Ref::TRANSACTION_TYPE_REVERSAL => 'Reversed',
                default => 'Adjusted',
            };

            return '<a class="govuk-link" href="' . $url . '">' . $status . '</a>';
        }

        return $status;
    }
}
