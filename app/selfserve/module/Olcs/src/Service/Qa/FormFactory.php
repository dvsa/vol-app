<?php

namespace Olcs\Service\Qa;

use Common\Form\QaForm;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormFactory
{
    /** @var ServiceLocatorInterface */
    private $serviceLocator;

    /**
     * Create service instance
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormFactory
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create an instance of QaForm
     *
     * @param string $formName
     *
     * @return QaForm
     */
    public function create($formName)
    {
        $form = $this->serviceLocator->get('Helper\Form')->createForm($formName);

        $dataHandlerMappings = [
            'ecmt_st_international_journeys' => 'QaEcmtShortTermInternationalJourneysDataHandler',
            'ecmt_st_annual_trips_abroad' => 'QaEcmtShortTermAnnualTripsAbroadDataHandler',
        ];

        $isValidHandlerMappings = [
            'ecmt_st_international_journeys' => 'QaEcmtShortTermInternationalJourneysIsValidHandler',
            'ecmt_st_annual_trips_abroad' => 'QaEcmtShortTermAnnualTripsAbroadIsValidHandler',
        ];

        foreach ($dataHandlerMappings as $type => $serviceName) {
            $form->registerDataHandler(
                $type,
                $this->serviceLocator->get($serviceName)
            );
        }

        foreach ($isValidHandlerMappings as $type => $serviceName) {
            $form->registerIsValidHandler(
                $type,
                $this->serviceLocator->get($serviceName)
            );
        }

        return $form;
    }
}
