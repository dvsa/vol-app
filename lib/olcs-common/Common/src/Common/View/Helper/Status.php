<?php

/**
 * Status view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Common\View\Helper;

use Common\Util\Escape;
use Laminas\View\Helper\AbstractHelper;
use Common\RefData;

/**
 * Status view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Status extends AbstractHelper
{
    /**
     * Get the HTML to render a status
     *
     * @param array $status Status as a ref data array or array containing colour and value fields
     *
     * @return string HTML
     */
    public function __invoke(array $status)
    {
        if (!empty($status['colour']) && !empty($status['value'])) {
            // if colour and value defined, render what's been given
            return $this->render($status['value'], $status['colour']);
        }

        if (empty($status['id']) || empty($status['description'])) {
            // no data provided
            return '';
        }

        // render based on the status
        return $this->render($status['description'], $this->getColourForStatus($status['id']));
    }

    /**
     * Get the HTML to render
     *
     * @param string $value  Value to display
     * @param string $colour Class name to apply to the element
     *
     * @return string HTML
     */
    protected function render($value, $colour)
    {
        return sprintf('<strong class="govuk-tag govuk-tag--%s">%s</strong>', Escape::html($colour), Escape::html($value));
    }

    /**
     * Get the color class to use
     *
     * @param string $id A status ref data id
     *
     * @return string Class name
     */
    protected function getColourForStatus($id)
    {
        $colors = [
            // Bus reg
            RefData::BUSREG_STATUS_ADMIN                        => 'grey',
            RefData::BUSREG_STATUS_REGISTERED                   => 'green',
            RefData::BUSREG_STATUS_REFUSED                      => 'grey',
            RefData::BUSREG_STATUS_CANCELLATION                 => 'orange',
            RefData::BUSREG_STATUS_WITHDRAWN                    => 'grey',
            RefData::BUSREG_STATUS_VARIATION                    => 'orange',
            RefData::BUSREG_STATUS_CNS                          => 'grey',
            RefData::BUSREG_STATUS_CANCELLED                    => 'grey',
            RefData::BUSREG_STATUS_NEW                          => 'orange',
            // EBSR

            // Licence
            RefData::LICENCE_STATUS_VALID                       => 'green',
            RefData::LICENCE_STATUS_SUSPENDED                   => 'orange',
            RefData::LICENCE_STATUS_CURTAILED                   => 'orange',
            RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION => 'green',
            RefData::LICENCE_STATUS_SURRENDERED                 => 'red',
            RefData::LICENCE_STATUS_REVOKED                     => 'red',
            RefData::LICENCE_STATUS_TERMINATED                  => 'red',
            RefData::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT     => 'red',
            RefData::LICENCE_STATUS_UNDER_CONSIDERATION         => 'orange',
            RefData::LICENCE_STATUS_GRANTED                     => 'orange',
            RefData::LICENCE_STATUS_WITHDRAWN                   => 'red',
            RefData::LICENCE_STATUS_REFUSED                     => 'red',
            RefData::LICENCE_STATUS_NOT_TAKEN_UP                => 'red',
            RefData::LICENCE_STATUS_CANCELLED                   => 'grey',
            // Application statuses
            RefData::APPLICATION_STATUS_NOT_SUBMITTED           => 'grey',
            RefData::APPLICATION_STATUS_GRANTED                 => 'orange',
            RefData::APPLICATION_STATUS_UNDER_CONSIDERATION     => 'orange',
            RefData::APPLICATION_STATUS_VALID                   => 'green',
            RefData::APPLICATION_STATUS_WITHDRAWN               => 'red',
            RefData::APPLICATION_STATUS_REFUSED                 => 'red',
            RefData::APPLICATION_STATUS_NOT_TAKEN_UP            => 'red',
            RefData::APPLICATION_STATUS_CANCELLED               => 'grey',
            // TM
            RefData::TRANSPORT_MANAGER_STATUS_CURRENT           => 'green',
            RefData::TRANSPORT_MANAGER_STATUS_DISQUALIFIED      => 'red',
            RefData::TRANSPORT_MANAGER_STATUS_REMOVED           => 'green',
            // Feature Toggles
            RefData::FT_ACTIVE                                  => 'green',
            RefData::FT_INACTIVE                                => 'red',
            RefData::FT_CONDITIONAL                             => 'orange',
            // Permit
            RefData::PERMIT_APP_STATUS_AWAITING_FEE             => 'red',
            RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION      => 'orange',
            RefData::PERMIT_APP_STATUS_ISSUING                  => 'green',
            RefData::PERMIT_APP_STATUS_WITHDRAWN                => 'red',
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED        => 'grey',
            RefData::PERMIT_APP_STATUS_UNSUCCESSFUL             => 'grey',
            RefData::PERMIT_APP_STATUS_CANCELLED                => 'grey',
            RefData::PERMIT_APP_STATUS_VALID                    => 'green',
            RefData::PERMIT_APP_STATUS_FEE_PAID                 => 'green',
            RefData::IRHP_PERMIT_STATUS_PRINTED                 => 'green',
            RefData::PERMIT_VALID                               => 'green',
        ];

        return empty($colors[$id]) ? 'grey' : $colors[$id];
    }
}
