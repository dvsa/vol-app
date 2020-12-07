<?php

namespace Olcs\Service\Qa\ViewGenerator;

use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;

class IrhpApplicationViewGenerator implements ViewGeneratorInterface
{
    const ERR_NOT_SUPPORTED = 'IrhpApplicationViewGenerator does not support redirection requests';

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'permits/single-question';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormName()
    {
        return 'QaForm';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalViewVariables(MvcEvent $mvcEvent, array $result)
    {
        return [
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'applicationRef' => $result['additionalViewData']['applicationReference']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleRedirectionRequest(Redirect $redirect, $destinationName)
    {
        throw new RuntimeException(self::ERR_NOT_SUPPORTED);
    }
}
