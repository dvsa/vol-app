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

        $sections = $this->getPreselectedSectionsForType($value['submissionType']);
        if (isset($value['sections'])) {
            $sections = array_merge($value['sections'], $sections);
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
                            return array_merge(
                                $data['sections'],
                                $this->getMandatorySections()
                            );
                        }
                    )
                )
            ),
            'validators' => array(
                $this->getValidator(),
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
        switch($submissionType)
        {
            case 'submission_type_o_bus_reg':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_auth',
                    'submission_section_trma',
                    'submission_section_fire',
                    'submission_section_brad',
                    'submission_section_trac',
                    'submission_section_tbus',
                    'submission_section_llhi',
                    'submission_section_regd',
                    'submission_section_mtdh'
                ]);
            case 'submission_type_o_clo_fep':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_wflf'
                ]);
            case 'submission_type_o_clo_g':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_ctud',
                    'submission_section_inuc',
                    'submission_section_intm',
                    'submission_section_advt',
                    'submission_section_auth',
                    'submission_section_trma',
                    'submission_section_cnec',
                    'submission_section_fire',
                    'submission_section_llhi',
                    'submission_section_mtdh',
                    'submission_section_objs',
                    'submission_section_fnin'
                ]);
            case 'submission_type_o_clo_psv':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_ctud',
                    'submission_section_inuc',
                    'submission_section_auth',
                    'submission_section_trma',
                    'submission_section_cnec',
                    'submission_section_fire',
                    'submission_section_tbus',
                    'submission_section_llhi',
                    'submission_section_regd',
                    'submission_section_mtdh',
                    'submission_section_objs',
                    'submission_section_fnin'
                ]);
            case 'submission_type_o_env':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_ochi',
                    'submission_section_ctud',
                    'submission_section_inuc',
                    'submission_section_intm',
                    'submission_section_advt',
                    'submission_section_auth',
                    'submission_section_trma',
                    'submission_section_cnec',
                    'submission_section_fire',
                    'submission_section_llhi',
                    'submission_section_cpoh',
                    'submission_section_terp',
                    'submission_section_site',
                    'submission_section_plpm',
                    'submission_section_acom',
                    'submission_section_vaes',
                    'submission_section_envc',
                    'submission_section_reps',
                    'submission_section_objs',
                    'submission_section_fnin',
                    'submission_section_maps'
                ]);
            case 'submission_type_o_irfo':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_trma',
                    'submission_section_fire',
                    'submission_section_mtdh'
                ]);
            case 'submission_type_o_mlh':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_ctud',
                    'submission_section_inuc',
                    'submission_section_intm',
                    'submission_section_advt',
                    'submission_section_llan',
                    'submission_section_alau',
                    'submission_section_ltca',
                    'submission_section_auth',
                    'submission_section_trma',
                    'submission_section_cnec',
                    'submission_section_fire',
                    'submission_section_llhi',
                    'submission_section_mlhh',
                    'submission_section_mtdh',
                    'submission_section_fnin'
                ]);
            case 'submission_type_o_otc':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_opce',
                    'submission_section_ctud',
                    'submission_section_ituc',
                    'submission_section_llan',
                    'submission_section_ltca',
                    'submission_section_cusu',
                    'submission_section_trma',
                    'submission_section_fire',
                    'submission_section_llhi',
                    'submission_section_mtdh',
                    'submission_section_proh',
                    'submission_section_cpoh',
                    'submission_section_anth',
                    'submission_section_pens',
                    'submission_section_comp',
                    'submission_section_fnin'
                ]);
            case 'submission_type_o_tm':
                return array_merge($this->getMandatorySections(), $this->getDefaultSections(), [
                    'submission_section_inuc',
                    'submission_section_trma',
                    'submission_section_cnec',
                    'submission_section_fire',
                    'submission_section_objs'
                ]);
        }

        return $this->getDefaultSections();
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
            'submission_section_case',
            'submission_section_pers',
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
            'submission_section_case',
            'submission_section_msin',
            'submission_section_pers',
            'submission_section_preh',
            'submission_section_misc',
            'submission_section_annx'
        ];
    }
}
