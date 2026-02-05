<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateAddresses;
use Permits\Data\Mapper\MapperManager;

/**
 * Class AddressDetailsController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class AddressDetailsController extends AbstractSurrenderController
{
    protected $form;

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $formData = (array)$request->getPost();
        } else {
            $formData = Mapper\Licence\Surrender\AddressDetails::mapFromResult($this->data['surrender']['licence']);
        }

        $this->form = $this->getForm('Licence\Surrender\Addresses')
            ->setData($formData);

        $hasProcessed = $this->formHelper->processAddressLookupForm($this->form, $request);

        if (!$hasProcessed && $request->isPost()) {
            if ($this->form->isValid()) {
                $response = $this->save($formData);

                if ($response === true) {
                    return $this->redirect()->toRoute(
                        'licence/surrender/review-contact-details/GET',
                        [],
                        [],
                        true
                    );
                }

                return $this->redirect()->refresh();
            }
        }

        $params = $this->getViewVariables();

        return $this->renderView($params);
    }

    /**
     * Save form
     *
     * @param array $formData Form Data
     *
     * @return bool
     */
    protected function save(array $formData): bool
    {
        $dtoData =
            [
                'id' => $this->licenceId,
                'partial' => false,
            ] +
            Mapper\Lva\Addresses::mapFromForm($formData);

        $response = $this->handleCommand(UpdateAddresses::create($dtoData));

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage('licence.surrender.contact-details-changed');
            return true;
        }

        $this->flashMessengerHelper->addUnknownError();
        return false;
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => 'lva.section.title.addresses',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'form' => $this->form,
            'backLink' => $this->getLink('licence/surrender/review-contact-details/GET'),
        ];
    }
}
