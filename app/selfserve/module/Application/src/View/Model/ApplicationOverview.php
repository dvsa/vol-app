<?php

namespace Dvsa\Olcs\Application\View\Model;

use Olcs\View\Model\LvaOverview;
use Common\RefData;
use Olcs\View\Model\LvaOverviewSection;

/**
 * Application Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOverview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'application/pages/overview-application';

    /**
     * ApplicationOverview constructor. Sets the overview data
     *
     * @param array $data           Data array
     * @param array $sections       Sections array
     * @param null  $submissionForm Submission form
     */
    public function __construct($data, array $sections = [], $submissionForm = null)
    {
        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('licNo', $data['licence']['licNo'] ?? '');
        $this->setVariable('createdOn', date('d F Y', strtotime((string) $data['createdOn'])));
        $this->setVariable('status', $data['status']['id']);
        $this->setVariable('submissionForm', $submissionForm);
        $this->setVariable('receivedDate', $data['receivedDate']);
        $this->setVariable('completionDate', $data['targetCompletionDate']);
        $this->setVariable('canCancel', $data['status']['id'] === RefData::APPLICATION_STATUS_NOT_SUBMITTED);

        $completedSections = array_filter(
            $sections,
            fn($section) => isset($section['complete']) && $section['complete'] == true
        );
        $this->setVariable('progressX', count($completedSections));
        $this->setVariable('progressY', count($sections));

        parent::__construct($data, $sections);
    }

    /**
     * @param mixed ...$args
     * @return ApplicationOverviewSection
     */
    protected function newSectionModel(...$args): LvaOverviewSection
    {
        return new ApplicationOverviewSection(...$args);
    }
}
