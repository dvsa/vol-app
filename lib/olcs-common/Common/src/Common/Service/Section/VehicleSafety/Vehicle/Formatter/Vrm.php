<?php

/**
 * Vrm
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Section\VehicleSafety\Vehicle\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FormatterPluginManagerInterface;

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

        return '<a class="govuk-link" href="' . $this->urlHelper->fromRoute(
            null,
            [
                'child_id' => $data['id'],
                'action' => $action
            ],
            [],
            true
        ) . '">' . $data['vrm'] . '</a>';
    }
}
