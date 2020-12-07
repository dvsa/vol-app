<?php

namespace Olcs\Service\Qa\ViewGenerator;

use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;

class IrhpPermitApplicationViewGenerator implements ViewGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'permits/single-question-bilateral';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormName()
    {
        return 'QaBilateralForm';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalViewVariables(MvcEvent $mvcEvent, array $result)
    {
        $additionalViewData = $result['additionalViewData'];
        $previousStepSlug = $additionalViewData['previousStepSlug'];

        $routeMatch = $mvcEvent->getRouteMatch();
        $currentUriParams = $routeMatch->getParams();
        if (is_null($previousStepSlug)) {
            $backUri = IrhpApplicationSection::ROUTE_PERIOD;
            $backUriParams = [
                'id' => $currentUriParams['id'],
                'country' => $additionalViewData['countryCode']
            ];
        } else {
            $backUri = $routeMatch->getMatchedRouteName();
            $backUriParams = $currentUriParams;
            $backUriParams['slug'] = $previousStepSlug;
        }

        return [
            'backUri' => $backUri,
            'backUriParams' => $backUriParams,
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'countryName' => $additionalViewData['countryName']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleRedirectionRequest(Redirect $redirect, $destinationName)
    {
        $mappings = [
            'OVERVIEW' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'CANCEL' => IrhpApplicationSection::ROUTE_CANCEL_APPLICATION,
        ];

        if (!isset($mappings[$destinationName])) {
            throw new RuntimeException(
                sprintf(
                    'IrhpPermitApplicationViewGenerator does not support a destination name of %s',
                    $destinationName
                )
            );
        }

        $routeParams = $redirect->getController()->params()->fromRoute();

        $routeOptions = [];
        if ($destinationName == 'CANCEL') {
            $routeOptions = [
                'query' => [
                    'fromBilateralCabotage' => '1',
                    'ipa' => $routeParams['irhpPermitApplication'],
                    'slug' => $routeParams['slug']
                ]
            ];
        }

        return $redirect->toRoute(
            $mappings[$destinationName],
            [
                'id' => $routeParams['id']
            ],
            $routeOptions
        );
    }
}
