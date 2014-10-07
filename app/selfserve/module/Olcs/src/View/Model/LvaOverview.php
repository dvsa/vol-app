<?php

/**
 * LVA Overview View Model
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\View\Model;

use Common\View\AbstractViewModel;
use Olcs\View\Model\OverviewSection;

/**
 * LVA Overview View Model
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class LvaOverview extends AbstractViewModel
{
    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array())
    {
        $overviewSections = [];
        foreach ($sections as $section) {
            $overviewSections[] = new OverviewSection($section, $data);
        }

        $this->setVariable('sections', $overviewSections);
    }
}
