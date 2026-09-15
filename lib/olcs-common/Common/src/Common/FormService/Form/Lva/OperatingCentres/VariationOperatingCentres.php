<?php

namespace Common\FormService\Form\Lva\OperatingCentres;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentres extends AbstractOperatingCentres
{
    public function __construct(
        FormHelperService $formHelper,
        protected AuthorizationService $authService,
        protected $tableBuilder,
        protected FormServiceManager $formServiceLocator,
        private TranslationHelperService $translator
    ) {
        parent::__construct($formHelper);
    }

    protected $mainTableConfigName = 'lva-variation-operating-centres';

    #[\Override]
    protected function alterForm(Form $form, array $params)
    {
        $this->formServiceLocator->get('lva-variation')->alterForm($form);

        parent::alterForm($form, $params);

        $licence = $params['licence'];

        if ($form->get('data')->has('totAuthHgvVehiclesFieldset')) {
            $hint = $this->translator->translateReplace('current-authorisation-hint', [$licence['totAuthHgvVehicles'] ?? 0]);
            $form->get('data')->get('totAuthHgvVehiclesFieldset')->get('totAuthHgvVehicles')->setOption('hint-below', $hint);
        }

        if ($form->get('data')->has('totAuthLgvVehiclesFieldset')) {
            $hint = $this->translator->translateReplace('current-authorisation-hint', [$licence['totAuthLgvVehicles'] ?? 0]);
            $form->get('data')->get('totAuthLgvVehiclesFieldset')->get('totAuthLgvVehicles')->setOption('hint-below', $hint);
        }

        if ($form->get('data')->has('totAuthTrailersFieldset')) {
            $hint = $this->translator->translateReplace('current-authorisation-hint', [$licence['totAuthTrailers']]);
            $form->get('data')->get('totAuthTrailersFieldset')->get('totAuthTrailers')->setOption('hint-below', $hint);
        }

        if ($form->get('data')->has('totCommunityLicencesFieldset')) {
            $this->formHelper->disableElement($form, 'data->totCommunityLicencesFieldset->totCommunityLicences');
        }

        return $form;
    }
}
