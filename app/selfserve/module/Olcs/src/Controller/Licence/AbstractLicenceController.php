<?php

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractExternalController;

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
abstract class AbstractLicenceController extends AbstractExternalController
{
    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'licence';

    /**
     * Get licence id
     *
     * @return int
     */
    protected function getLicenceId()
    {
        return $this->params('id');
    }

    /**
     * Get type of licence data
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        return $this->getEntityService('Licence')->getTypeOfLicenceData($this->getLicenceId());
    }

    /**
     * Go to overview page
     *
     * @param int $licenceId
     * @return \Zend\Http\Response
     */
    protected function goToOverview($licenceId)
    {
        return $this->redirect()->toRoute('licence', array('id' => $licenceId));
    }

    /**
     * Redirect to the next section
     *
     * @param string $currentSection
     */
    protected function goToNextSection($currentSection)
    {
        $sections = $this->getAccessibleSections();

        $index = array_search($currentSection, $sections);

        // If there is no next section
        if (!isset($sections[$index + 1])) {
            return $this->goToOverview($this->getLicnenceId());
        } else {
            return $this->redirect()
                ->toRoute('licence/' . $sections[$index + 1], array('id' => $this->getLicnenceId()));
        }
    }
}
