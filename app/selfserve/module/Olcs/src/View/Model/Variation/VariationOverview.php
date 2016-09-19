<?php

/**
 * Variation Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Variation;

use Olcs\View\Model\LvaOverview;
use Common\RefData;

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
     * VariationOverview constructor. Sets the overview data
     *
     * @param array $data           Data array
     * @param array $sections       Sections array
     * @param null  $submissionForm Submission form
     */
    public function __construct($data, array $sections = array(), $submissionForm = null)
    {
        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('licNo', isset($data['licence']['licNo']) ? $data['licence']['licNo'] : '');
        $this->setVariable('createdOn', date('d F Y', strtotime($data['createdOn'])));
        $this->setVariable('status', $data['status']['id']);
        $this->setVariable('receivedDate', $data['receivedDate']);
        $this->setVariable('completionDate', $data['targetCompletionDate']);
        $this->setVariable('submissionForm', $submissionForm);
        $this->setVariable('canCancel', $data['status']['id'] === RefData::APPLICATION_STATUS_NOT_SUBMITTED);

        parent::__construct($data, $sections);
    }
}
