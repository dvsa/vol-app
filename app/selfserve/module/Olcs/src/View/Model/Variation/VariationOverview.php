<?php

/**
 * Variation Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Variation;

use Olcs\View\Model\LvaOverview;

/**
 * Variation Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOverview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'overview-variation';

    protected $sectionModel = 'Variation\\VariationOverviewSection';

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
        $this->setVariable('receivedDate', $data['receivedDate']);
        $this->setVariable('completionDate', $data['targetCompletionDate']);

        parent::__construct($data, $sections);
    }
}
