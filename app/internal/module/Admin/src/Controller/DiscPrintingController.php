<?php

/**
 * Disc Printing Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Common\Service\Entity\LicenceEntityService;
use Dvsa\Olcs\Transfer\Command\GoodsDisc\PrintDiscs as PrintDiscsGoodsDto;
use Dvsa\Olcs\Transfer\Command\PsvDisc\PrintDiscs as PrintDiscsPsvDto;
use Dvsa\Olcs\Transfer\Command\GoodsDisc\ConfirmPrinting as ConfirmPrintingGoodsDto;
use Dvsa\Olcs\Transfer\Command\PsvDisc\ConfirmPrinting as ConfirmPrintingPsvDto;
use Dvsa\Olcs\Transfer\Query\DiscSequence\DiscPrefixes as DiscPrefixesQry;
use Dvsa\Olcs\Transfer\Query\DiscSequence\DiscsNumbering as DiscsNumberingQry;
use Admin\Data\Mapper\DiscPrinting as DiscPrintingMapper;
use \Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\Controller\Traits\GenericRenderView;
use Common\Controller\Traits\GenericMethods;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Common\Util\FlashMessengerTrait;

/**
 * Disc Printing Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscPrintingController extends ZendAbstractActionController implements LeftViewProvider
{
    use GenericRenderView,
        GenericMethods,
        FlashMessengerTrait;

    /**
     * Discs on page
     */
    const DISCS_ON_PAGE = 6;

    /**
     * Where we store generated lists of discs in JR
     */
    const STORAGE_PATH = 'discs';

    private $hasDiscsToPrint = false;

    /**
     * Index action
     *
     * @todo this needs cleaning up, the part where it attaches the disc-printing-popup js file needs refactoring
     * to work as other sections do when showing a modal. We can't have JS files with hardcoded urls etc
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $form = $this->getForm('DiscPrinting');

        $inlineScripts = ['disc-printing'];

        if ($this->getRequest()->isPost()) {
            $this->formPost($form, 'processForm');
            if ($this->hasDiscsToPrint) {
                $inlineScripts[] = 'disc-printing-popup';
            }
        }
        $successStatus = $this->params()->fromRoute('success', null);
        $params = [
            'form' => $form
        ];
        if (!$this->getRequest()->isPost()) {
            if ($successStatus !== null) {
                if ($successStatus == 1) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addCurrentSuccessMessage('Disc printing successful');
                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addCurrentErrorMessage('Disc printing failed');
                }
            }
        }
        $view = new ViewModel($params);
        $this->loadScripts($inlineScripts);
        $view->setTemplate('pages/disc-printing/disc-printing-form');

        return $this->renderView($view, 'admin_disc-printing.pageHeader');
    }

    /**
     * Process form
     *
     * @param array $data
     * @return mixed
     */
    protected function processForm($data, $form)
    {
        $params = $this->getFlattenParams($data);

        $this->hasDiscsToPrint = false;
        // get discs to print
        if ($params['operatorType'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $dataToSend = [
                'licenceType'  => $params['licenceType'],
                'startNumber'  => $params['startNumber'],
                'discSequence' =>  $params['discSequence']
            ];
            $dtoClass = PrintDiscsPsvDto::class;
        } else {
            $dataToSend = [
                'niFlag'      => $params['niFlag'],
                'licenceType' => $params['licenceType'],
                'startNumber' => $params['startNumber'],
                'discSequence' =>  $params['discSequence']
            ];
            $dtoClass = PrintDiscsGoodsDto::class;
        }

        $response = $this->handleCommand($dtoClass::create($dataToSend));

        if ($response->isClientError()) {
            $errors = DiscPrintingMapper::mapFromErrors($form, $response->getResult()['messages']);
            foreach ($errors as $message) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($message);
            }
        }
        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
        if ($response->isOk()) {
            $form->get('queueId')->setValue($response->getResult()['id']['queue']);
            $this->hasDiscsToPrint = true;
        }
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

        $operatorLocation = $this->params()->fromPost('operator-location');
        if ($operatorLocation['niFlag'] === 'Y') {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'operator-type');
        }

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
        $operatorType = $form->has('operator-type') ?
            $form->get('operator-type')->get('goodsOrPsv')->getValue() : '';
        $discSequence = $form->get('prefix')->get('discSequence')->getValue();
        $startNumberEntered = $form->get('discs-numbering')->get('startNumber')->getValue();

        $numbering = $this->processDiscNumbering(
            $niFlag,
            $licenceType,
            $operatorType,
            $discSequence,
            $startNumberEntered
        );

        if (isset($numbering['endNumber'])) {
            $form->get('discs-numbering')->get('endNumber')->setValue($numbering['endNumber']);
        }
        if (isset($numbering['endNumberIncreased'])) {
            $form->get('discs-numbering')->get('endNumberIncreased')->setValue($numbering['endNumberIncreased']);
        }
        if (isset($numbering['totalPages'])) {
            $form->get('discs-numbering')->get('totalPages')->setValue($numbering['totalPages']);
        }

        return $form;
    }

    /**
     * Get disc numbering data
     *
     * @return Zend\ViewModel\JsonModel
     */
    public function discNumberingAction()
    {
        $params = $this->getFlattenParams();
        $viewResults = $this->processDiscNumbering(
            $params['niFlag'],
            $params['licenceType'],
            $params['operatorType'],
            $params['discSequence'],
            $params['startNumber']
        );

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
    protected function processDiscNumbering(
        $niFlag,
        $licenceType,
        $operatorType,
        $discSequence,
        $startNumberEntered = null
    ) {
        $retv = [];

        $data = [
            'niFlag' => $niFlag,
            'operatorType' => $operatorType,
            'licenceType' => $licenceType,
            'discSequence' => $discSequence,
            'startNumberEntered' => $startNumberEntered
        ];

        $response = $this->handleQuery(DiscsNumberingQry::create($data));

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $retv = $response->getResult()['results'];
        }

        return $retv;
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
     * Check parameters and populate disc prefixes
     *
     * @return array
     */
    protected function populateDiscPrefixes()
    {
        $params = $this->getFlattenParams();
        return $this->getDiscPrefixes($params['niFlag'], $params['operatorType'], $params['licenceType']);
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
        $data = [
            'niFlag' => $niFlag,
            'operatorType' => $operatorType,
            'licenceType' => $licenceType
        ];

        $response = $this->handleQuery(DiscPrefixesQry::create($data));

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        $retv = [];
        if ($response->isOk()) {
            $retv = DiscPrintingMapper::mapFromResultForPrefixes($response->getResult()['results']);
        }

        return $retv;
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
        return DiscPrintingMapper::mapFromForm($params);
    }

    /**
     * Confirm disc printing
     *
     */
    public function confirmDiscPrintingAction()
    {
        $params = $this->getFlattenParams();
        $retv = [];

        if ($params['operatorType'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $dtoClass = ConfirmPrintingPsvDto::class;
            $data = [
                'licenceType' => $params['licenceType'],
                'startNumber' => $params['startNumber'],
                'endNumber' => $params['endNumber'],
                'discSequence' => $params['discSequence'],
                'isSuccessfull' => $params['isSuccessfull'],
                'queueId' => $params['queueId'],
            ];
        } else {
            $dtoClass = ConfirmPrintingGoodsDto::class;
            $data = [
                'niFlag' => $params['niFlag'],
                'licenceType' => $params['licenceType'],
                'startNumber' => $params['startNumber'],
                'endNumber' => $params['endNumber'],
                'discSequence' => $params['discSequence'],
                'isSuccessfull' => $params['isSuccessfull'],
                'queueId' => $params['queueId'],
            ];
        }

        $response = $this->handleCommand($dtoClass::create($data));

        if ($response->isClientError()) {
            foreach ($response->getResult()['messages'] as $message) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($message);
            }
        }
        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
        if (!$response->isOk()) {
            $retv['status'] = 500;
        }

        return new JsonModel($retv);
    }

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-printing',
                'navigationTitle' => 'Printing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
