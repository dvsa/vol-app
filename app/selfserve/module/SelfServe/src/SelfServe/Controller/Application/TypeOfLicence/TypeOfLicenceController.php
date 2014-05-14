<?php

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

use SelfServe\Controller\Application\ApplicationController;

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends ApplicationController
{
    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = 'Licence';

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->goToFirstSubSection();
    }

    /**
     * Get licence data
     *
     * @param array $properties
     * @return array
     */
    protected function getLicenceData($properties = array())
    {
        $properties = array_merge(
            array('id', 'version'),
            $properties
        );

        return parent::getLicenceData($properties);
    }
}
