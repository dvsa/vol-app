<?php

namespace Olcs\Controller\Traits;

/**
 * Class PublicationControllerTrait
 * @package Olcs\Controller
 */
trait PublicationControllerTrait
{
    /**
     * Holds the publishing helper
     *
     * @var \Olcs\Service\Utility\PublicationHelper
     */
    protected $publicationHelper;

    /**
     * Get the publication helper
     *
     * @return \Olcs\Helper\PublicationHelper
     */
    protected function getPublicationHelper()
    {
        if (empty($this->publicationHelper)) {
            $service = $this->getServiceLocator()->get('Olcs\Service\Utility\PublicationHelper');
            $this->publicationHelper = $service;
        }

        return $this->publicationHelper;
    }
}
