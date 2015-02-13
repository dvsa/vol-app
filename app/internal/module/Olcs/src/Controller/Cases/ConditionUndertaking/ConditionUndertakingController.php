<?php

/**
 * CaseConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Cases\ConditionUndertaking;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Common\Service\Entity\ConditionUndertakingEntityService;

/**
 * ConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionUndertakingController extends OlcsController\CrudAbstract implements CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'condition';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'ConditionUndertakingForm';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'ConditionUndertaking';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_conditions_undertakings';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['case'];

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
                'base',
            )
        )
    );

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'case',
            'attachedTo',
            'conditionType',
            'operatingCentre' => array(
                'children' => array(
                    'address' => array(
                        'children' => array(
                            'countryCode'
                        )
                    )
                )
            ),
            'addedVia',
        )
    );

    const CONDITION_TYPE_CONDITION = 'cdt_con';
    const CONDITION_TYPE_UNDERTAKING = 'cdt_und';

    const ATTACHED_TO_LICENCE = 'cat_lic';
    const ATTACHED_TO_OPERATING_CENTRE = 'cat_oc';

    /**
     * Added extra method called after setting form data
     *
     * @param Form $form
     * @return Form
     */
    public function alterFormBeforeValidation($form)
    {
        $form->setLabel($form->getLabel() . ' Conditions / Undertakings');

        $this->getAdapter()->alterForm($form, $this->getParentId());

        return $form;
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        return $this->getAdapter()->processDataForForm($data);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        $data['fields']['addedVia'] = ConditionUndertakingEntityService::ADDED_VIA_CASE;

        return parent::processSave(
            $this->getAdapter()->processDataForSave($data, $this->getParentId())
        );
    }

    /**
     * Get the relevant lva adapter
     *
     * @return \Common\Controller\Lva\Interfaces\ConditionsUndertakingsAdapterInterface
     */
    protected function getAdapter()
    {
        $lva = $this->getLva();

        return $this->getServiceLocator()->get(ucfirst($lva) . 'ConditionsUndertakingsAdapter');
    }

    /**
     * Check what the lva type is for the given case
     *
     * @return string
     * @throws \Exception
     */
    protected function getLva()
    {
        $case = $this->getCase();

        // @NOTE We must check application first, as an application case
        // can still have a licence id
        if (isset($case['application']) && !empty($case['application'])) {
            if ($case['application']['isVariation']) {
                return 'variation';
            }

            return 'application';
        }

        if (isset($case['licence']) && !empty($case['licence'])) {
            return 'licence';
        }

        throw new \Exception('Can\'t determine parent resource from case');
    }

    /**
     * Grab either the licence or application id from the case
     *
     * @return int
     */
    protected function getParentId()
    {
        $case = $this->getCase();
        $lva = $this->getLva();

        if ($lva === 'licence') {
            return $case['licence']['id'];
        }

        return $case['application']['id'];
    }
}
