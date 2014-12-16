<?php

/**
 * External Application People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\OrganisationEntityService;

/**
 * External Application People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController
{
    use ApplicationControllerTrait {
        ApplicationControllerTrait::postSave as commonPostSave;
    }

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * Action executed after save
     *
     * @param string $section
     */
    protected function postSave($section)
    {
        $this->commonPostSave($section);
        $this->setOperatorName();
    }

    /**
     * Set operator name
     */
    private function setOperatorName()
    {
        $orgId = $this->getCurrentOrganisationId();
        $orgData = $this->getServiceLocator()
                    ->get('Entity\Organisation')
                    ->getType($orgId);
        $name = null;
        if ($orgData['type']['id'] === OrganisationEntityService::ORG_TYPE_SOLE_TRADER) {
            $person = $this->getServiceLocator()
                    ->get('Entity\Person')
                    ->getFirstForOrganisation($orgId);
            $name = $person['forename'] . ' ' . $person['familyName'];
        } elseif ($orgData['type']['id'] === OrganisationEntityService::ORG_TYPE_PARTNERSHIP) {
            $persons = $this->getServiceLocator()->get('Entity\Person')->getAllForOrganisation($orgData['id'], 'all');
            switch (count($persons['Results'])) {
                case 0:
                    $name = '';
                    break;
                case 1:
                    $name = $persons['Results'][0]['person']['forename'] . ' ' .
                        $persons['Results'][0]['person']['familyName'];
                    break;
                case 2:
                    $name = $persons['Results'][0]['person']['forename'] . ' ' .
                        $persons['Results'][0]['person']['familyName'] . ' & ' .
                        $persons['Results'][1]['person']['forename'] . ' ' .
                        $persons['Results'][1]['person']['familyName'];
                    break;
                default:
                    $name = $persons['Results'][0]['person']['forename'] . ' ' .
                        $persons['Results'][0]['person']['familyName'] . ' & Partners';
            }
        }
        if (!is_null($name)) {
            $data = [
                'name' => $name,
                'id' => $orgId,
                'version' => $orgData['version']
            ];
            $this->getServiceLocator()->get('Entity\Organisation')->save($data);
        }
    }
}
