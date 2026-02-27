<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\User\AgreeTerms as AgreeTermsCmd;
use Laminas\Http\Response;
use Olcs\Form\Model\Form\AgreeTerms as AgreeTermsForm;
use Permits\Data\Mapper\MapperManager;

class WelcomeController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'generic' => 'pages/welcome',
    ];

    protected $formConfig = [
        'generic' => [
            'confirmationForm' => [
                'formClass' => AgreeTermsForm::class
            ]
        ]
    ];

    protected $postConfig = [
        'generic' => [
            'command' => AgreeTermsCmd::class,
            'step' => 'index',
            'saveAndReturnStep' => 'index',
        ],
    ];

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        private readonly UrlHelperService $urlHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @return Response|void
     */
    #[\Override]
    public function checkConditionalDisplay()
    {
        if (isset($this->postParams['form-actions']['signOut'])) {
            return $this->conditionalDisplayNotMet('auth/logout');
        }

        if ($this->currentUser()->getIdentity()->hasAgreedTerms()) {
            return $this->conditionalDisplayNotMet('index');
        }
    }

    #[\Override]
    public function alterForm($form)
    {
        // inject link into terms agreed label
        $termsAgreed = $form->get('fields')->get('termsAgreed');

        $label = $this->translationHelper->translateReplace(
            $termsAgreed->getLabel(),
            [
                $this->urlHelper->fromRoute('terms-and-conditions')
            ]
        );

        $termsAgreed->setLabel($label);

        return parent::alterForm($form);
    }
}
