<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres as CommonLicenceOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see \OlcsTest\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentresTest
 */
class LicenceOperatingCentres extends CommonLicenceOperatingCentres
{
    protected $mainTableConfigName = 'lva-licence-operating-centres';

    private $lockElements = [
        'totAuthHgvVehiclesFieldset->totAuthHgvVehicles',
        'totAuthLgvVehiclesFieldset->totAuthLgvVehicles',
        'totAuthTrailersFieldset->totAuthTrailers',
    ];

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected $tableBuilder;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        $tableBuilder,
        FormServiceManager $formServiceLocator
    ) {
        parent::__construct($formHelper, $authService, $tableBuilder, $formServiceLocator);
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        $dataElement = $form->get('data');

        $this->formHelper->disableElements($dataElement);

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }

        foreach ($this->lockElements as $lockElementRef) {
            $lockElementRefComponents = explode('->', (string) $lockElementRef);
            $lockElement = $dataElement;
            foreach ($lockElementRefComponents as $elementRef) {
                if (null === $lockElement) {
                    break;
                }
                $lockElement = $lockElement->has($elementRef) ? $lockElement->get($elementRef) : null;
            }
            if (null !== $lockElement) {
                $this->formHelper->lockElement($lockElement, 'operating-centres-licence-locked');
            }
        }

        $this->removeStandardFormActions($form);
    }
}
