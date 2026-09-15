<?php

namespace Common\Controller\Lva;

use Common\Controller\Lva\Traits\CrudActionTrait;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Common Lva Abstract Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTypeOfLicenceController extends AbstractController
{
    use CrudActionTrait;

    protected string $baseRoute = 'lva-%s/type_of_licence';

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        protected ScriptFactory $scriptFactory
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Render for Index action
     *
     * @param \Laminas\Form\FormInterface $form Form
     *
     * @return \Common\View\Model\Section
     */
    protected function renderIndex($form)
    {
        $this->scriptFactory->loadFile('type-of-licence');

        return $this->render('type_of_licence', $form);
    }

    /**
     * Process error messages from API
     *
     * @param Form $form Form
     * @param array                    $errors Errors
     *
     * @return void
     */
    protected function mapErrors(Form $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['licenceType'])) {
            foreach ($errors['licenceType'][0] as $key => $message) {
                $formMessages['type-of-licence']['licence-type'][] = $key;
            }

            unset($errors['licenceType']);
        }

        if (isset($errors['goodsOrPsv'])) {
            foreach ($errors['goodsOrPsv'][0] as $key => $message) {
                $formMessages['type-of-licence']['operator-type'][] = $key;
            }

            unset($errors['licenceType']);
        }

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
