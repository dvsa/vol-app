<?php

/**
 * SubmissionSections Element, consisting of a submission type
 * select element and various checkbox elements.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Form\Element;

use Common\Form\Element\Button;
use Laminas\Form\Element as LaminasElement;
use Laminas\Form\ElementPrepareAwareInterface;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputProviderInterface;

/**
 * SubmissionSections
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSections extends LaminasElement implements ElementPrepareAwareInterface, InputProviderInterface
{
    /**
     * Select form element that contains values for submission type
     *
     * @var \Laminas\Form\Element\Select
     */
    protected $submissionType;

    /**
     * Array of checkbox elements suitable for submission type
     *
     * @var array
     */
    protected $sections;

    /**
     * Select button to submit the submission type, which dictates what
     * checkboxes are required.
     *
     * @var \Laminas\Form\Element\Button
     */
    protected $submissionTypeSubmit;

    /**
     * Hidden form element that contains transportManager Id
     *
     * @var \Laminas\Form\Element\Text
     */
    protected $transportManager;

    /**
     * Set transport manager
     *
     * @param \Laminas\Form\Element\Text $transportManager Transport Manager text element
     *
     * @return SubmissionSections
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;
        return $this;
    }

    /**
     * Get transport manager
     *
     * @return \Laminas\Form\Element\Text
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set submission type
     *
     * @param LaminasElement\Select $submissionType Submission type select element
     *
     * @return SubmissionSections
     */
    public function setSubmissionType($submissionType)
    {
        $this->submissionType = $submissionType;
        return $this;
    }

    /**
     * Get submission type
     *
     * @return \Laminas\Form\Element\Select
     */
    public function getSubmissionType()
    {
        return $this->submissionType;
    }

    /**
     * Set sections
     *
     * @param array $sections Array of submission sections to set
     *
     * @return SubmissionSections
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * Get sections from element
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Set submission type submit
     *
     * @param Button $submissionTypeSubmit Submission type submit button
     *
     * @return void
     */
    public function setSubmissionTypeSubmit($submissionTypeSubmit)
    {
        $this->submissionTypeSubmit = $submissionTypeSubmit;
    }

    /**
     * Get submission type submit button
     *
     * @return \Laminas\Form\Element\Button
     */
    public function getSubmissionTypeSubmit()
    {
        return $this->submissionTypeSubmit;
    }

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param FormInterface $form Form - not currently used
     */
    public function prepareElement(FormInterface $form): void
    {
        unset($form);
        $name = $this->getName();

        $this->getSubmissionType()->setName($name . '[submissionType]');

        $sections = $this->getSections()->getValueOptions();
        $m_sections = $this->getMandatorySections();

        $tm = $this->getTransportManager()->getValue();
        if (empty($tm)) {
            $sections = $this->removeTmSections($sections);
            foreach ($m_sections as $m_key) {
                $sections[$m_key] = ['label' => $sections[$m_key], 'selected' => 'selected', 'disabled' => true];
            }
        } else {
            // disable all but TM options
            $tmSections = $this->getAllTmSections();
            foreach ($sections as $key => $label) {
                if (!in_array($key, $tmSections)) {
                    unset($sections[$key]);
                } elseif (in_array($key, $m_sections)) {
                    $sections[$key] = ['label' => $label, 'selected' => 'selected', 'disabled' => true];
                }
            }
        }

        $this->getSections()->setValueOptions($sections);
        $this->getSections()->setOptions(['label_position' => 'append']);

        $this->getSections()->setName($name . '[sections]');
        $this->getSubmissionTypeSubmit()->setName($name . '[submissionTypeSubmit]');
        $this->getTransportManager()->setName($name . '[transportManager]');
    }

    /**
     * Removes TM sections from section list array
     *
     * @param array $sections Array of current sections from which to remove TM sections
     *
     * @return array
     */
    private function removeTmSections($sections)
    {
        $tmSections = $this->getTmOnlySections();
        foreach ($tmSections as $tmSection) {
            unset($sections[$tmSection]);
        }
        return $sections;
    }

    /**
     * Set value for element(s)
     *
     * @param array $value value to set the element to.
     *
     * @return SubmissionSections
     */
    public function setValue($value)
    {
        $this->getSubmissionType()->setValue($value['submissionType']);
        $sections = [];

        if (isset($value['submissionType'])) {
            if (!(isset($value['sections']))) {
                $sections = $this->getPreselectedSectionsForType($value['submissionType']);
            } else {
                if (isset($value['submissionTypeSubmit'])) {
                    $sections = $this->getPreselectedSectionsForType($value['submissionType']);
                    $this->addCssToDifference($value['sections'], $sections);
                } else {
                    // type not submitted
                    $sections = $value['sections'];
                }
            }
        }

        $this->getSections()->setValue($sections);

        return $this;
    }

    /**
     * Adds a class to highlight those options which were originally selected but not included in a new submission
     * type, when one is posted.
     *
     * @param array $postedSections     array of POSTed sections
     * @param array $newDefaultSections array of new default sections
     *
     * @return void
     */
    public function addCssToDifference($postedSections = [], $newDefaultSections = [])
    {
        $allSections = $this->getSections()->getValueOptions();
        foreach ($allSections as $key => $title) {
            if (in_array($key, $postedSections) && !in_array($key, $newDefaultSections)) {
                $allSections[$key] = ['label' => $title, 'value' => $key, 'label_attributes' => ['class' =>
                    'pre-selected']];
            }
        }
        $this->getSections()->setValueOptions($allSections);
    }

    /**
     * @return array
     */
    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($data) {
                                $sections = array_merge(
                                    $data['sections'] ?? [],
                                    $this->getMandatorySections()
                                );
                            return [
                                'submissionType' => $data['submissionType'],
                                'sections' => $sections
                            ];
                        }
                    ]
                ]
            ],
            'validators' => [
                [
                    'name' => \Olcs\Validator\SubmissionSection::class
                ]
            ]
        ];
    }

    /**
     * Returns the Preselected section keys for a given submission type
     *
     * @param string $submissionType Submission type reference to determine which sections to pre-select
     *
     * @return array
     */
    private function getPreselectedSectionsForType($submissionType)
    {
        switch ($submissionType) {
            case 'submission_type_o_bus_reg':
                $sections = [
                    'case-outline',
                    'people',
                    'annex',
                    'bus-reg-app-details',
                    'transport-authority-comments',
                    'total-bus-registrations',
                    'registration-details',
                ];
                break;
            case 'submission_type_o_clo_fep':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'waive-fee-late-fee'
                ];
                break;
            case 'submission_type_o_clo_g':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligence-unit-check',
                    'interim',
                    'advertisement',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-and-repute',
                    'local-licence-history',
                    'maintenance-tachographs-hours',
                    'objections',
                    'financial-information',
                    'oppositions'
                ];
                break;
            case 'submission_type_o_clo_psv':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligence-unit-check',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-and-repute',
                    'total-bus-registrations',
                    'local-licence-history',
                    'registration-details',
                    'maintenance-tachographs-hours',
                    'objections',
                    'financial-information',
                    'oppositions'
                ];
                break;
            case 'submission_type_o_env':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'operating-centres',
                    'conditions-and-undertakings',
                    'intelligence-unit-check',
                    'interim',
                    'advertisement',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-and-repute',
                    'local-licence-history',
                    'conviction-fpn-offence-history',
                    'te-reports',
                    'site-plans',
                    'planning-permission',
                    'applicants-comments',
                    'applicants-responses',
                    'visibility-access-egress-size',
                    'environmental-complaints',
                    'objections',
                    'financial-information',
                    'maps',
                    'oppositions'
                ];
                break;
            case 'submission_type_o_irfo':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'operating-centres',
                    'transport-managers',
                    'fitness-and-repute',
                    'maintenance-tachographs-hours'
                ];
                break;
            case 'submission_type_o_mlh_otc':
                $sections = [
                    'case-outline',
                    'most-serious-infringement',
                    'people',
                    'previous-history',
                    'operating-centres',
                    'conditions-and-undertakings',
                    'linked-licences-app-numbers',
                    'lead-tc-area',
                    'auth-requested-applied-for',
                    'transport-managers',
                    'continuous-effective-control',
                    'fitness-and-repute',
                    'linked-mlh-history',
                    'maintenance-tachographs-hours',
                    'financial-information'
                ];
                break;
            case 'submission_type_o_ni_tru':
                $sections = [
                    'case-outline',
                    'most-serious-infringement',
                    'people',
                    'previous-history',
                    'operating-centres',
                    'conditions-and-undertakings',
                    'linked-licences-app-numbers',
                    'current-submissions',
                    'transport-managers',
                    'maintenance-tachographs-hours',
                    'prohibition-history',
                    'conviction-fpn-offence-history',
                    'annual-test-history'
                ];
                break;
            case 'submission_type_o_mlh_clo':
                $sections = [
                    'lead-tc-area'
                ];
                break;
            case 'submission_type_o_otc':
                $sections = [
                    'case-outline',
                    'most-serious-infringement',
                    'people',
                    'previous-history',
                    'operating-centres',
                    'te-reports',
                    'linked-licences-app-numbers',
                    'current-submissions',
                    'transport-managers',
                    'maintenance-tachographs-hours',
                    'prohibition-history',
                    'conviction-fpn-offence-history',
                    'annual-test-history'
                ];
                break;
            case 'submission_type_o_tm':
                $sections = array_merge(
                    [
                        'case-outline',
                        'people',
                        'previous-history',
                        'other-issues',
                        'annex',
                        'intelligence-unit-check',
                        'transport-managers',
                        'continuous-effective-control',
                        'fitness-and-repute',
                        'oppositions'
                    ],
                    $this->getTmOnlySections()
                );
                break;
            case 'submission_type_o_schedule_41':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'operating-centres',
                    'conditions-and-undertakings',
                    'linked-licences-app-numbers',
                    'lead-tc-area',
                    'auth-requested-applied-for',
                    'site-plans',
                    'applicants-comments',
                    'applicants-responses',
                    'environmental-complaints',
                    'waive-fee-late-fee'
                ];
                break;
            case 'submission_type_o_impounding':
                $sections = [
                    'case-outline',
                    'people',
                    'previous-history',
                    'other-issues',
                    'annex',
                    'statements'
                ];
                break;
            default:
                $sections = [];
        }

        return array_merge(
            $this->getMandatorySections(),
            $sections
        );
    }

    /**
     * Returns the mandatory section keys for a given submission type
     *
     * @return array
     */
    private function getMandatorySections()
    {
        return [
            'case-summary',
            'case-outline',
            'people',
            'outstanding-applications'
        ];
    }

    /**
     * Gets list of Transport Manager specific sections.
     *
     * @note These may be removed by the controller/JS if the case type is NOT TM
     *
     * @return array
     */
    public function getTmOnlySections()
    {
        return [
            'tm-details',
            'tm-qualifications',
            'tm-responsibilities',
            'tm-other-employment',
            'tm-previous-history'
        ];
    }

    /**
     * Gets list of All Transport Manager sections.
     *
     * @note These may be removed by the controller/JS if the case type is NOT TM
     *
     * @return array
     */
    public function getAllTmSections()
    {
        return array_merge(
            [
                'case-outline',
                'most-serious-infringement',
                'intelligence-unit-check',
                'current-submissions',
                'continuous-effective-control',
                'fitness-and-repute',
                'other-issues',
                'annex'
            ],
            $this->getTmOnlySections()
        );
    }
}
