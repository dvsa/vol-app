<?php

namespace Dvsa\Olcs\Api\Service\Permits\Checkable;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Task\Task;

interface CheckableApplicationInterface
{
    /**
     * Return the open task associated with this application that represents the application submission, or null if
     * the task does not exist or has been closed
     *
     * @return Task|null
     */
    public function fetchOpenSubmissionTask();

    /**
     * Update the checked value for this application
     *
     * @param bool $checked
     */
    public function updateChecked($checked);

    /**
     * Return the camel case entity name to be used in the task creation upon submission of this application
     *
     * @return string
     */
    public function getCamelCaseEntityName();

    /**
     * Return the id of this application
     *
     * @return int
     */
    public function getId();

    /**
     * Return the task description to be used in the task creation upon submission of this application
     *
     * @return string
     */
    public function getSubmissionTaskDescription();

    /**
     * Return the licence instance associated with this application
     *
     * @return Licence
     */
    public function getLicence();

    /**
     * Whether the check is applicable to this application
     *
     * @return bool
     */
    public function requiresPreAllocationCheck();
}
