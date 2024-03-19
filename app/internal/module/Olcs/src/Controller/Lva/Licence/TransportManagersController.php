<?php

/**
 * Internal Licence Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Licence\DeleteUpdateOptOutTmLetter;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Licence Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersController extends Lva\AbstractTransportManagersController implements
    LicenceControllerInterface,
    TransportManagerControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'internal';
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param QueryService $queryService
     * @param CommandService $commandService
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param TransportManagerHelperService $transportManagerHelper
     * @param LicenceTransportManagerAdapter $lvaAdapter
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        QueryService $queryService,
        CommandService $commandService,
        AnnotationBuilder $transferAnnotationBuilder,
        TransportManagerHelperService $transportManagerHelper,
        LicenceTransportManagerAdapter $lvaAdapter,
        $navigation
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $queryService,
            $commandService,
            $transferAnnotationBuilder,
            $transportManagerHelper,
            $lvaAdapter
        );
        $this->navigation = $navigation;
    }

    /**
     * Return different delete message if last TM.
     *
     * @return string The modal message key.
     */
    protected function getDeleteMessage()
    {

        if ($this->isLastTmLicence()) {
            return 'internal-delete.final-tm.confirmation.text';
        }

        return 'delete.confirmation.text';
    }

    protected function getDeleteConfirmationForm()
    {
        if ($this->isLastTmLicence()) {
            return 'LastTransportManagerDeleteConfirmation';
        }
        return parent::getDeleteConfirmationForm();
    }

    protected function delete()
    {

        if (!$this->isLastTmLicence()) {
            return parent::delete();
        }

        /**
        * @var \Laminas\Http\Request $request
        */
        $request = $this->getRequest();

        $formHelper = $this->formHelper;
        /**
        * @var \Common\Form\Form $form
        */
        $form = $formHelper->createFormWithRequest($this->getDeleteConfirmationForm(), $request);

        $form->setData((array)$request->getPost());

        if ($form->isValid()) {
            $data = $form->getData();
            $ids = explode(',', $this->params('child_id'));

            return $this->handleCommand(
                Delete::create(
                    [
                    'ids' => $ids,
                    'yesNo' => $data["YesNoRadio"]["yesNo"],
                    ]
                )
            );
        }

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteConfirmationForm(), $form, $params);
    }
}
