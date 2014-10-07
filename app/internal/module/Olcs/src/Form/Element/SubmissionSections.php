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
use Zend\InputFilter\InputProviderInterface;

/**
 * SubmissionSections
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSections extends ZendElement implements ElementPrepareAwareInterface, InputProviderInterface
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
    protected $sections;

    /**
     * Select button to submit the submission type, which dictates what
     * checkboxes are required.
     *
     * @var Button
     */
    protected $submissionTypeSubmit;

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
     * @param Array $sections
     *
     * @return $this
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * @return Array
     */
    public function getSections()
    {
        return $this->sections;
    }

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
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return mixed
     */
    public function prepareElement(FormInterface $form)
    {
        $name = $this->getName();

        $this->getSubmissionType()->setName($name . '[submissionType]');

        $sections = $this->getSections()->getValueOptions();
        $m_sections = $this->getMandatorySections();

        foreach ($m_sections as $m_key) {
            $sections[$m_key] = ['label' => $sections[$m_key], 'selected' => 'seleected', 'disabled' => true];
        }
        $this->getSections()->setValueOptions($sections);
        $this->getSections()->setOptions(['label_position'=>'append']);

        $this->getSections()->setName($name . '[sections]');
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

        $this->getSubmissionType()->setValue($value['submissionType']);
        $sections = [];
        $optionalSections = [];

        if (isset($value['submissionType'])) {

            if (isset($value['submissionTypeSubmit'])) {
                if (!(isset($value['sections']))) {
                    // no sections set so just add preselected
                    $sections = $this->getPreselectedSectionsForType($value['submissionType']);
                } else {
                    // merge preselected with those already selected
                    $sections = array_merge(
                        $value['sections'],
                        $this->getPreselectedSectionsForType($value['submissionType'])
                    );
                }
            } else {
                // type not submitted
                if (!(isset($value['sections']))) {
                    $sections = $this->getPreselectedSectionsForType($value['submissionType']);
                } else {
                    $sections = $value['sections'];
                }
            }
        }

        $sections = array_unique($sections);
        $this->getSections()->setValue($sections);

        return $this;
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInput()}.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => true,
            'filters' => array(
                array(
                    'name'    => 'Callback',
                    'options' => array(
                        'callback' => function ($data) {
                                $sections = array_merge(
                                    $data['sections'],
                                    $this->getMandatorySections()
                                );
                            return [
                                'submissionType' => $data['submissionType'],
                                'sections' => $sections
                            ];
                        }
                    )
                )
            )
        );
    }

    /**
     * Returns the Preselected  section keys for a given submission type
     *
     * @param string $submissionType
     * @return array
     */
    private function getPreselectedSectionsForType($submissionType)
    {
        switch($submissionType) {
            case 'submission_type_o_bus_reg':
                $sections = [
                    'operating-centres',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'fitness-repute',
                    'bus-reg-app-details',
                    'transport-authority-comments',
                    'total-bus-registrations',
                    'local-licence-history',
                    'registration-details',
                    'maintenance-tachographs-hours'
                ];
                break;
            case 'submission_type_o_clo_fep':
                $sections = [
                    'waive-fee-late-fee'
                ];
                break;
            case 'submission_type_o_clo_g':
                $sections = [
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligent-unit-check',
                    'interim',
                    'advertisement',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-repute',
                    'local-licence-history',
                    'maintenance-tachographs-hours',
                    'objections',
                    'financial-information'
                ];
                break;
            case 'submission_type_o_clo_psv':
                $sections = [
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligent-unit-check',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-repute',
                    'total-bus-registrations',
                    'local-licence-history',
                    'registration-details',
                    'maintenance-tachographs-hours',
                    'objections',
                    'financial-information'
                ];
                break;
            case 'submission_type_o_env':
                $sections = [
                    'operating-centres',
                    'operating-centre-history',
                    'conditions-and-undertakings',
                    'intelligent-unit-check',
                    'interim',
                    'advertisement',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-repute',
                    'local-licence-history',
                    'conviction-fpn-offence-history',
                    'te-reports',
                    'site-plans',
                    'planning-permission',
                    'applicants-comments',
                    'visibility-access-egress-size',
                    'environmental-complaints',
                    'representations',
                    'objections',
                    'financial-information',
                    'maps'
                ];
                break;
            case 'submission_type_o_irfo':
                $sections = [
                    'operating-centres',
                    'transport-managers',
                    'fitness-repute',
                    'maintenance-tachographs-hours'
                ];
                break;
            case 'submission_type_o_mlh':
                $sections = [
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligent-unit-check',
                    'interim',
                    'advertisement',
                    'linked-licences-app-numbers',
                    'all-auths',
                    'lead-tc-area',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-repute',
                    'local-licence-history',
                    'linked-mlh-history',
                    'maintenance-tachographs-hours',
                    'financial-information'
                ];
                break;
            case 'submission_type_o_otc':
                $sections = [
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligent-unit-check',
                    'linked-licences-app-numbers',
                    'lead-tc-area',
                    'current-submissions',
                    'transport-managers',
                    'fitness-repute',
                    'local-licence-history',
                    'maintenance-tachographs-hours',
                    'prohibition-history',
                    'conviction-fpn-offence-history',
                    'annual-test-history',
                    'penalties',
                    'complaints',
                    'financial-information'
                ];
                break;
            case 'submission_type_o_tm':
                $sections = [
                    'intelligent-unit-check',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-repute',
                    'objections'
                ];
                break;
            default:
                $sections = [];
        }

        return array_merge($this->getMandatorySections(), $this->getDefaultSections(), $sections);
    }

    /**
     * Returns the mandatory section keys for a given submission type
     *
     * @param string $submissionType
     * @return array
     */
    private function getMandatorySections()
    {
        return [
            'introduction',
            'case-summary',
            'case-outline',
            'persons',
        ];
    }

    /**
     * Gets list of default sections that ALL submission types must have
     *
     * @return array
     */
    private function getDefaultSections()
    {
        return [
            'case-outline',
            'most-serious-infringement',
            'persons',
            'previous-history',
            'other-issues',
            'annex'
        ];
    }
}
