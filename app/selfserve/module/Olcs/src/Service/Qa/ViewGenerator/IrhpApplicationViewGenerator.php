<?php

namespace Olcs\Service\Qa\ViewGenerator;

use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;

class IrhpApplicationViewGenerator implements ViewGeneratorInterface
{
    public const ERR_NOT_SUPPORTED = 'IrhpApplicationViewGenerator does not support redirection requests';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getTemplateName()
    {
        return 'permits/single-question';
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
    #[\Override]
    public function handleRedirectionRequest(Redirect $redirect, $destinationName): never
    {
        throw new RuntimeException(self::ERR_NOT_SUPPORTED);
    }
}
