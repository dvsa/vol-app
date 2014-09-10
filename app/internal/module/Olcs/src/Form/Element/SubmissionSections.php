<?php

/**
 * SubmissionSections Element, consisting of a submission type
 * select element and various checkbox elements.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Form\Element;

use Zend\Form\Element as ZendElement;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

/**
 * SubmissionSections
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSections extends ZendElement
{



    /**
     * Select form element that contains values for submission type
     *
     * @var Select
     */
    protected $submissionType;

    /**
     * Array of checkbox elements suitable for submission type
     *
     * @var Array
     */
    protected $submissionSections;



    /**
     * @param Array $submissionSections
     *
     * @return $this
     */
    public function setSubmissionSections($submissionSections)
    {
        $this->submissionSections = $submissionSections;
        return $this;
    }

    /**
     * @return Array
     */
    public function getSubmissionSections()
    {
        return $this->submissionSections;
    }

    /**
     * @param \Common\Form\Elements\Custom\Select $submissionType
     *
     * @return $this
     */
    public function setSubmissionType($submissionType)
    {
        $this->submissionType = $submissionType;
        return $this;
    }

    /**
     * @return \Common\Form\Elements\Custom\Select
     */
    public function getSubmissionType()
    {
        return $this->submissionType;
    }



}
