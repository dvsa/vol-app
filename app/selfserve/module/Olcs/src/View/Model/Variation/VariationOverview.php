<?php

namespace Olcs\View\Model\Variation;

use Olcs\View\Model\LvaOverview;
use Common\RefData;
use Olcs\View\Model\LvaOverviewSection;

class VariationOverview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'overview-variation';

    /**
     * VariationOverview constructor. Sets the overview data
     *
     * @param array $data           Data array
     * @param array $sections       Sections array
     * @param null  $submissionForm Submission form
     */
    public function __construct($data, array $sections = [], $submissionForm = null)
    {
        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('licNo', $data['licence']['licNo'] ?? '');
        $this->setVariable('createdOn', date('d F Y', strtotime($data['createdOn'])));
        $this->setVariable('status', $data['status']['id']);
        $this->setVariable('receivedDate', $data['receivedDate']);
        $this->setVariable('completionDate', $data['targetCompletionDate']);
        $this->setVariable('submissionForm', $submissionForm);
        $this->setVariable('canCancel', $data['status']['id'] === RefData::APPLICATION_STATUS_NOT_SUBMITTED);

        parent::__construct($data, $sections);
    }

    /**
     * @param mixed ...$args
     * @return VariationOverviewSection
     */
    protected function newSectionModel(...$args): LvaOverviewSection
    {
        return new VariationOverviewSection(...$args);
    }
}
