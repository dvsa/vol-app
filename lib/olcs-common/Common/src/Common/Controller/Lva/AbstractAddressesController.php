<?php

namespace Common\Controller\Lva;

use Common\Data\Mapper;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Shared logic between Addresses controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractAddressesController extends AbstractController
{
    protected static $mapCmdUpdateAddress = [
        'licence' => TransferCmd\Licence\UpdateAddresses::class,
        'application' => TransferCmd\Application\UpdateAddresses::class,
        'variation' => TransferCmd\Variation\UpdateAddresses::class,
    ];

    protected string $section = 'addresses';

    protected string $baseRoute = 'lva-%s/addresses';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Process action - Index
     *
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        //  prepare form data
        if ($request->isPost()) {
            $formData = (array)$request->getPost();
        } else {
            //  get api data
            $response = $this->handleQuery(
                TransferQry\Licence\Addresses::create(['id' => $this->getLicenceId()])
            );

            if (!$response->isOk()) {
                return $this->notFoundAction();
            }

            $formData = Mapper\Lva\Addresses::mapFromResult($response->getResult());
        }

        //  get phone contacts from api
        $apiPhoneContactsData = [];
        if (isset($formData['correspondence']['id'])) {
            $apiPhoneContactsData = $this->getPhoneContacts($formData['correspondence']['id']);
        }

        /** @var \Common\Form\Form $form */
        $form = $this->formServiceManager->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm(
                [
                    'typeOfLicence' => $this->getTypeOfLicenceData(),
                    'corrPhoneContacts' => $apiPhoneContactsData,
                ]
            )
            ->setData($formData);

        $this->alterFormForLva($form);

        $hasProcessed = $this->formHelper->processAddressLookupForm($form, $request);

        if (!$hasProcessed && $request->isPost() && $this->isValid($form, $formData)) {
            $response = $this->save($formData);
            if ($response !== null) {
                if ($response === true) {
                    return $this->completeSection('addresses');
                }

                return $response;
            }
        }

        $this->scriptFactory->loadFiles(['forms/addresses']);

        return $this->render('addresses', $form);
    }

    /**
     * Get Correspondence Phone contacts
     *
     * @param int $contactDetailsId Contact Details Id
     *
     * @return array
     */
    protected function getPhoneContacts($contactDetailsId = null)
    {
        return [];
    }

    /**
     * Check is form valid
     *
     * @param Form  $form     Form
     * @param array $formData Form data
     *
     * @return bool
     */
    protected function isValid(Form $form, array $formData)
    {
        $this->disableConsultantValidation($form, $formData);

        return $form->isValid();
    }

    /**
     * Save form
     *
     * @param array $formData Form Data
     *
     * @return array|bool|null
     */
    protected function save(array $formData)
    {
        $dtoData =
            [
                'id' => $this->getIdentifier(),
                'partial' => false,
            ] +
            Mapper\Lva\Addresses::mapFromForm($formData);

        $cmdClass = static::$mapCmdUpdateAddress[$this->lva];
        $response = $this->handleCommand($cmdClass::create($dtoData));

        if ($response->isOk()) {
            return true;
        }

        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'];

            $error = '';
            foreach ($messages as $message) {
                if ('ERR_TA_NI_APP' === $message) {
                    $error = $message;
                    break;
                }
            }

            if ($error !== '' && $error !== '0') {
                $this->flashMessengerHelper->addCurrentErrorMessage($error);
            } else {
                $this->flashMessengerHelper->addUnknownError();
            }
        } elseif ($response->isServerError()) {
            $this->flashMessengerHelper->addUnknownError();
        }

        return null;
    }

    /**
     * Disable consultant fields validation
     *
     * @param Form  $form Form
     * @param array $data Data
     */
    private function disableConsultantValidation(Form $form, array $data): void
    {
        if (!isset($data['consultant']) || $data['consultant']['add-transport-consultant'] !== 'N') {
            return;
        }

        $this->formHelper->disableValidation(
            $form->getInputFilter()->get('consultant')
        );
        $this->formHelper->disableValidation(
            $form->getInputFilter()->get('consultantAddress')
        );
    }
}
