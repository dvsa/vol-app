<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class SlaIndicator
 *
 * @package Olcs\View\Helper
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SlaIndicator extends AbstractHelper
{
    /**
     * @param $data
     * @param $targetDate
     *
     * @return string
     */
    public function __invoke()
    {
        return $this;
    }

    public function hasTargetBeenMet($date = null, $targetDate = null)
    {
        return '<span class="status grey">Inactive</span>';
    }
}
