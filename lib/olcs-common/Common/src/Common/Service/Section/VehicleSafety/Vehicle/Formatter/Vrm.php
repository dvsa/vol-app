<?php

/**
 * Vrm
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Section\VehicleSafety\Vehicle\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FormatterPluginManagerInterface;
use Common\Util\Escape;

/**
 * Vrm
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Vrm implements FormatterPluginManagerInterface
{
    public $urlHelper;

    public function __construct(UrlHelperService $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Format an cell
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $action = 'edit';

        if (isset($column['action-type'])) {
            $action = $column['action-type'] . '-' . $action;
        }

        // The anchor is developer-authored markup, so it is returned raw; the row value interpolated
        // into it is user-supplied and must be escaped. The href comes from the route builder rather
        // than from the row, so it is left as the builder produced it.
        return '<a class="govuk-link" href="' . $this->urlHelper->fromRoute(
            null,
            [
                'child_id' => $data['id'],
                'action' => $action
            ],
            [],
            true
        ) . '">' . Escape::html($data['vrm']) . '</a>';
    }
}
