<?php

namespace Common\View\Helper;

use Common\RefData;
use Laminas\View\Helper\AbstractHelper;

/**
 * Transport Manager Application Status view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerApplicationStatus extends AbstractHelper
{
    protected static $statusColors = [
        RefData::TMA_STATUS_INCOMPLETE => 'red',
        RefData::TMA_STATUS_DETAILS_SUBMITTED => 'red',
        RefData::TMA_STATUS_DETAILS_CHECKED => 'red',
        RefData::TMA_STATUS_AWAITING_SIGNATURE => 'orange',
        RefData::TMA_STATUS_TM_SIGNED => 'orange',
        RefData::TMA_STATUS_OPERATOR_APPROVED => 'orange',
        RefData::TMA_STATUS_OPERATOR_SIGNED => 'green',
        RefData::TMA_STATUS_POSTAL_APPLICATION => 'green',
        RefData::TMA_STATUS_RECEIVED => 'green',
    ];

    /**
     * Generate HTML to display a Transport Manager Application Status
     *
     * @param int    $statusId    Status Id
     * @param string $description Description
     *
     * @return string HTML
     */
    public function render($statusId, $description)
    {
        $statusClass = self::$statusColors[$statusId] ?? '';

        if (!isset($description) || $description === '') {
            return '';
        }

        return sprintf(
            '<strong class="govuk-tag govuk-tag--%s">%s</strong>',
            $statusClass,
            $this->getView()->translate($description)
        );
    }

    /**
     * Generate HTML to display a Transport Manager Application Status
     *
     * @param int    $statusId    Status Id
     * @param string $description Description
     *
     * @return string HTML
     */
    public function __invoke($statusId, $description)
    {
        return $this->render($statusId, $description);
    }
}
