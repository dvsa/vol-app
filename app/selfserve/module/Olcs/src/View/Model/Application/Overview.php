<?php

/**
 * Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Application;

use Common\View\AbstractViewModel;
use Olcs\View\Model\OverviewSection;

/**
 * Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Overview extends AbstractViewModel
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'application/overview';

    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array())
    {
        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('createdOn', date('d F Y', strtotime($data['createdOn'])));
        $this->setVariable('status', $data['status']['id']);

        $overviewSections = array();

        foreach ($sections as $section) {
            $overviewSections[] = new OverviewSection($section, $data);
        }

        $this->setVariable('sections', $overviewSections);
    }
}
