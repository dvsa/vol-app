<?php
/**
 * Organisation Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class OrganisationController extends AbstractRestfulController
{
    public function get($id)
    {
        $service = $this->getServiceLocator()->get('OrganisationServiceFactory');

        $data = $service->getOrganisationData(intval($id));

        if (empty($data)) {
            $this->response->setStatusCode(404);
            $data = array('error' => 'Specified resource could not be found');
        }

        return new JsonModel($data);
    }
}
