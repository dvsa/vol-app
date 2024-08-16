<?php

/**
 * TrailersController.php
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Mvc\MvcEvent;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class TrailersController
 *
 * {@inheritdoc}
 *
 * @package Olcs\Controller\Lva\Licence
 *
 * @author  Josh Curtis <josh.curtis@valtech.co.uk>
 */
class TrailersController extends Lva\AbstractTrailersController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'external';

    protected LicenceLvaAdapter $lvaAdapter;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     * @param ScriptFactory $scriptFactory
     * @param DateHelperService $dateHelper
     * @param QuerySender $querySender
     * @param LicenceLvaAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        ScriptFactory $scriptFactory,
        DateHelperService $dateHelper,
        QuerySender $querySender,
        LicenceLvaAdapter $lvaAdapter
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $tableFactory,
            $scriptFactory,
            $dateHelper,
            $querySender
        );
        $this->lvaAdapter = $lvaAdapter;
    }

    /**
     * Prevent access to NI
     *
     * @param MvcEvent $e event
     *
     * @return array|null|\Laminas\Http\Response
     */
    public function onDispatch(MvcEvent $e)
    {
        return $this->fetchDataForLva()['niFlag'] === 'Y'
            ? $this->notFoundAction()
            : parent::onDispatch($e);
    }
}
