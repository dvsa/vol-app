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
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;

/**
 * SubmissionSections
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSections extends ZendElement implements ElementPrepareAwareInterface
{

    /**
     * Select form element that contains values for submission type
     *
     * @var Select
     */
    protected $submissionType;

    /**
     * Select button to submit the submission type, which dictates what
     * checkboxes are required.
     *
     * @var Button
     */
    protected $submissionTypeSubmit;

    /**
     * @param \Olcs\Form\Element\Button $submissionTypeSubmit
     */
    public function setSubmissionTypeSubmit($submissionTypeSubmit)
    {
        $this->submissionTypeSubmit = $submissionTypeSubmit;
    }

    /**
     * @return \Olcs\Form\Element\Button
     */
    public function getSubmissionTypeSubmit()
    {
        return $this->submissionTypeSubmit;
    }

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

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return mixed
     */
    public function prepareElement(FormInterface $form)
    {
        $name = $this->getName();
        $this->getSubmissionType()->setName($name . '[submission_type]');
        $this->getSubmissionSections()->setName($name . '[submission_sections]');
        $this->getSubmissionTypeSubmit()->setName($name . '[submissionTypeSubmit]');
    }

    /**
     * Set value for element(s)
     *
     * @param array $value
     * @return void|ZendElement
     */
    public function setValue($value)
    {
        $this->getSubmissionType()->setValue($value['submission_type']);
    }
}
