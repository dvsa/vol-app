<?php
/**
 * Disc Printing Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Admin\Controller;

use Admin\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Disc Printing Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

class DiscPrintingController extends AbstractController
{

    /**
     * Discs on page
     */
    const DISCS_ON_PAGE = 6;

    /**
     * Index action
     *
     * @return Zend\ViewModel\ViewModel
     */
    public function indexAction()
    {
        $form = $this->getForm('admin_disc-printing');

        $this->formPost($form, 'processForm');

        $this->pageTitle = 'admin_disc-printing.pageHeader';
        $inlineScripts = ['disc-printing'];
        if ($this->getRequest()->isPost() && $form->isValid()) {
            $inlineScripts[] = 'disc-printing-popup';
        }
        $successStatus = $this->params()->fromRoute('success', null);
        $view = new ViewModel(
            [
                'form' => $form,
                'successStatus' => $successStatus
            ]
        );
        $this->loadScripts($inlineScripts);
        $view->setTemplate('disc-printing/index');
        return $this->renderView($view);
    }

    /**
     * Process form
     *
     * @param array $data
     * @return mixed
     */
    protected function processForm($data)
    {
        $params = $this->getFlattenParams($data);

        // get disc prefix using disc sequence and licence type
        $discSequenceService = $this->getServiceLocator()->get('Admin\Service\Data\DiscSequence');
        $discPrefix = $discSequenceService->getDiscPrefix($params['discSequence'], $params['licenceType']);

        // get discs to print
        $goodsDiscService = $this->getServiceLocator()->get('Admin\Service\Data\GoodsDisc');
        $discsToPrint = $goodsDiscService->getDiscsToPrint(
            $params['niFlag'],
            $params['operatorType'],
            $params['licenceType'],
            $discPrefix
        );

        // set printing status ON
        $goodsDiscService->setIsPrintingOn($discsToPrint);

    }

    /**
     * Alter form when we have all data set
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterFormBeforeValidation($form)
    {

        // we don't need Special Restricted Licence for discs numbering
        $licenceTypes = $form->get('licence-type')->get('licenceType')->getValueOptions();
        unset($licenceTypes['ltyp_sr']);
        $form->get('licence-type')->get('licenceType')->setValueOptions($licenceTypes);

        return $form;
    }

    /**
     * Alter form when we have all data set
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function postSetFormData($form)
    {

        // populate disc prefixes
        $discPrefixes = $this->populateDiscPrefixes();
        $form->get('prefix')->get('discSequence')->setValueOptions($discPrefixes);

        // get requered values from form
        $niFlag = $form->get('operator-location')->get('niFlag')->getValue();
        $licenceType = $form->get('licence-type')->get('licenceType')->getValue();
        $operatorType = $form->get('operator-type')->get('goodsOrPsv')->getValue();
        $discSequence = $form->get('prefix')->get('discSequence')->getValue();
        $startNumberEntered = $form->get('discs-numbering')->get('startNumber')->getValue();

        // get disc prefix using disc sequence and licence type
        $discSequenceService = $this->getServiceLocator()->get('Admin\Service\Data\DiscSequence');
        $discPrefix = $discSequenceService->getDiscPrefix($discSequence, $licenceType);

        // set up start number validator
        $goodsDiscNumberValidator = $this->getServiceLocator()->get('goodsDiscStartNumberValidator');
        $numbering = $this->processDiskNumbering(
            $niFlag,
            $licenceType,
            $operatorType,
            $discPrefix,
            $discSequence,
            $startNumberEntered
        );
        if (isset($numbering['startNumber'])) {
            $goodsDiscNumberValidator->setOriginalStartNumber($numbering['startNumber']);
        }

        $startNumberValidatorChain = $form
                                        ->getInputFilter()
                                        ->get('discs-numbering')
                                        ->get('startNumber')
                                        ->getValidatorChain();
        $startNumberValidatorChain->attach($goodsDiscNumberValidator);

        if (isset($numbering['endNumber'])) {
            $form->get('discs-numbering')->get('endNumber')->setValue($numbering['endNumber']);
        }
        if (isset($numbering['totalPages'])) {
            $form->get('discs-numbering')->get('totalPages')->setValue($numbering['totalPages']);
        }

        return $form;
    }

    /**
     * Get disc prefixes
     *
     * @return Zend\ViewModel\JsonModel
     */
    public function discPrefixesListAction()
    {
        return new JsonModel($this->populateDiscPrefixes());
    }

    /**
     * Get disc numbering data
     *
     * @return Zend\ViewModel\JsonModel
     */
    public function discNumberingAction()
    {
        $params = $this->getFlattenParams();
        $flProcess = true;
        $viewResults = [];

        // checking params which needed to calculate goods discs start/end numbers,
        // we can't process further without having it defined
        if (!$params['niFlag'] || !$params['operatorType'] || !$params['licenceType'] ||
            !$params['discSequence'] || !$params['discPrefix']) {
            $flProcess = false;
        }

        // calculate start and end numbers, number of pages
        if ($flProcess) {
            $viewResults = $this->processDiskNumbering(
                $params['niFlag'],
                $params['licenceType'],
                $params['operatorType'],
                $params['discPrefix'],
                $params['discSequence'],
                $params['startNumber']
            );
        }

        return new JsonModel($viewResults);
    }

    /**
     * Fetch disc numbering data, validate start number and increase numbers if necessary
     *
     * @param string $niFlag
     * @param string $licenceType
     * @param string $operatorType
     * @param string $discPrefix
     * @param string $discSequence
     * @param int $startNumberEntered
     * @return array
     */
    protected function processDiskNumbering(
        $niFlag,
        $licenceType,
        $operatorType,
        $discPrefix,
        $discSequence,
        $startNumberEntered = null
    ) {
        $retv = [];

        if (!$niFlag || !$licenceType || !$operatorType || !$discPrefix || !$discSequence) {
            return $retv;
        } 
        $discSequenceService = $this->getServiceLocator()->get('Admin\Service\Data\DiscSequence');
        $goodsDiscService = $this->getServiceLocator()->get('Admin\Service\Data\GoodsDisc');

        // calculate start and end numbers, number of pages
        $retv['startNumber'] = $discSequenceService->getDiscNumber($discSequence, $licenceType);
        $retv['discsToPrint'] = count(
            $goodsDiscService->getDiscsToPrint($niFlag, $operatorType, $licenceType, $discPrefix)
        );
        $retv['endNumber'] = (int) ($retv['discsToPrint'] ? $retv['startNumber'] + $retv['discsToPrint'] : 0);
        /*
         * we have two end numbers, one original, which calculated based on start number entered by user
         * and another one calculated by rounding up to nearest integer divided by 6. that's because
         * there are numbers already printed on the discs pages, 6 discs pere page, and even we need to print
         * only one disc, other numbers will be used and voided.
         */
        $retv['originalEndNumber'] = $retv['endNumber'];
        if ($retv['endNumber']) {
            while (($retv['endNumber'] - $retv['startNumber'] + 1) % self::DISCS_ON_PAGE) {
                $retv['endNumber']++;
            }
        }
        $retv['totalPages'] = $retv['discsToPrint'] ? ceil($retv['discsToPrint'] / self::DISCS_ON_PAGE) : 0;

        // if we received start number this means that user changed this value and we need to validate it
        // do not allow to decrease start number
        if ($startNumberEntered && $startNumberEntered < $retv['startNumber']) {
            $retv['error'] = 'Decreasing the start number is not permitted';
        } elseif ($startNumberEntered && $startNumberEntered > $retv['startNumber']) {
            // increasing start and end numbers
            $delta = $startNumberEntered - $retv['startNumber'];
            $retv['startNumber'] = $startNumberEntered;
            $retv['endNumber'] += $delta;
        }
        return $retv;
    }

    /**
     * Get list of disc prefixes
     *
     * @param string $niFlag
     * @param string $operatorType
     * @param string $licenceType
     * @return array
     */
    protected function getDiscPrefixes($niFlag, $operatorType, $licenceType)
    {
        $discSequenceService = $this->getServiceLocator()->get('Admin\Service\Data\DiscSequence');
        $prefixes = $discSequenceService->fetchListOptions(
            [
            'niFlag' => $niFlag,
            'goodsOrPsv' => $operatorType,
            'licenceType' => $licenceType
            ]
        );

        $retv = array();
        foreach ($prefixes as $id => $result) {
            $retv[] = array(
                'value' => $id,
                'label' => $result
            );
        }
        return $retv;
    }

    /**
     * Check parameters and populate disc prefixes
     *
     * @return array
     */
    protected function populateDiscPrefixes()
    {
        $params = $this->getFlattenParams();
        if (($params['niFlag'] == 'N' && !$params['operatorType']) || !$params['licenceType']) {
            return [];
        }

        return $this->getDiscPrefixes($params['niFlag'], $params['operatorType'], $params['licenceType']);
    }

    /**
     * Flatten provided array or parameters
     *
     * @param array $data
     * @return array
     */
    protected function getFlattenParams($data = null)
    {
        $params = is_array($data) ? $data : $this->getRequest()->getPost()->toArray();
        $flattenParams = [];
        $flattenParams['niFlag'] =
            isset($params['operator-location']['niFlag']) ? $params['operator-location']['niFlag'] :
            (isset($params['niFlag']) ? $params['niFlag'] : '');
        $flattenParams['operatorType'] =
            isset($params['operator-type']['goodsOrPsv']) ? $params['operator-type']['goodsOrPsv'] :
            (isset($params['operatorType']) ? $params['operatorType'] : '');
        $flattenParams['licenceType'] =
            isset($params['licence-type']['licenceType']) ? $params['licence-type']['licenceType'] :
            (isset($params['licenceType']) ? $params['licenceType'] : '');
        $flattenParams['startNumber'] =
            isset($params['discs-numbering']['startNumber']) ? $params['discs-numbering']['startNumber'] :
            (isset($params['startNumberEntered']) ? $params['startNumberEntered'] : null);
        $flattenParams['discSequence'] =
            isset($params['prefix']['discSequence']) ? $params['prefix']['discSequence'] :
            (isset($params['discSequence']) ? $params['discSequence'] : '');
        $flattenParams['discPrefix'] =isset($params['discPrefix']) ? $params['discPrefix'] : '';
        $flattenParams['isSuccessfull'] =isset($params['isSuccessfull']) ? $params['isSuccessfull'] : '';
        $flattenParams['endNumber'] =isset($params['endNumber']) ? $params['endNumber'] : '';

        return $flattenParams;
    }

    /**
     * Confirm disc printing
     *
     */
    public function confirmDiscPrintingAction()
    {
        $params = $this->getFlattenParams();
        $retv = [];
        $discSequenceService = $this->getServiceLocator()->get('Admin\Service\Data\DiscSequence');
        $goodsDiscService = $this->getServiceLocator()->get('Admin\Service\Data\GoodsDisc');
        $discsToPrint = $goodsDiscService->getDiscsToPrint(
            $params['niFlag'],
            $params['operatorType'],
            $params['licenceType'],
            $params['discPrefix']
        );
        try {
            if ($params['isSuccessfull']) {
                $goodsDiscService->setIsPrintingOffAndAssignNumber($discsToPrint, $params['startNumber']);
                $discSequenceService->setNewStartNumber(
                    $params['licenceType'],
                    $params['discSequence'],
                    $params['endNumber'] + 1
                );
            } else {
                $goodsDiscService->setIsPrintingOff($discsToPrint);
            }
        } catch (\Exception $e) {
            $retv['status'] = 500;
        }
        return new JsonModel($retv);
    }
}
