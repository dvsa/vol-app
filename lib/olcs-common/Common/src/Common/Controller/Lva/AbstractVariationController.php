<?php

namespace Common\Controller\Lva;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractVariationController extends AbstractController
{
    use Traits\CreateVariationTrait;

    protected $processingCreateVariation;

    protected FlashMessengerHelperService $flashMessengerHelper;

    /**
     * @param $processingCreateVariation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected TranslationHelperService $translationHelper,
        $processingCreateVariation,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->processingCreateVariation = $processingCreateVariation;
        $this->flashMessengerHelper = $flashMessengerHelper;
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action
     *
     * @return \Common\View\Model\Section
     */
    #[\Override]
    public function indexAction()
    {
        $form = $this->processForm();

        if (! ($form instanceof Form)) {
            return $form;
        }

        return $this->render(
            'create-variation-confirmation',
            $form,
            ['sectionText' => $this->translationHelper->translate('markup-licence-changes-confirmation-text')]
        );
    }
}
