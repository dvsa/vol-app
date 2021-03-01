<?php

namespace Olcs\Service\Qa\ViewGenerator;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;

interface ViewGeneratorInterface
{
    /**
     * Get the template name to be used by this view generator
     *
     * @return string
     */
    public function getTemplateName();

    /**
     * Get an array of additional view variables to be used in the view
     *
     * @param MvcEvent $mvcEvent
     * @param array $result
     *
     * @return array
     */
    public function getAdditionalViewVariables(MvcEvent $mvcEvent, array $result);

    /**
     * Return a Response object in accordance with the supplied backend destination name
     *
     * @param Redirect $redirect
     * @param string $destinationName
     *
     * @return Response
     */
    public function handleRedirectionRequest(Redirect $redirect, $destinationName);
}
