<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\VariationConditionsUndertakingsAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Variation Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractConditionsUndertakingsController implements
    VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param TableFactory $tableFactory
     * @param StringHelperService $stringHelper
     * @param VariationConditionsUndertakingsAdapter $lvaAdapter
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        StringHelperService $stringHelper,
        VariationConditionsUndertakingsAdapter $lvaAdapter,
        protected $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $lvaAdapter
        );
    }

    /**
     * Action - Restore CU
     *
     * @NOTE At the moment this method can only be called from variation
     *
     * @return \Laminas\Http\Response
     */
    public function restoreAction()
    {
        $id = $this->params('child_id');
        $ids = explode(',', $id);

        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Variation\RestoreListConditionUndertaking::create(
                ['id' => $this->getIdentifier(), 'ids' => $ids]
            )
        );

        $flashMessenger = $this->flashMessengerHelper;
        if ($response->isOk()) {
            if (count($response->getResult()['messages'])) {
                $flashMessenger->addSuccessMessage('generic-restore-success');
            } else {
                $flashMessenger->addInfoMessage('generic-nothing-updated');
            }
        } else {
            $flashMessenger->addErrorMessage('unknown-error');
        }

        return $this->redirect()->toRouteAjax(
            $this->getBaseRoute(),
            [$this->getIdentifierIndex() => $this->getIdentifier()]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    protected function getRenderVariables()
    {
        return ['title' => null];
    }
}
