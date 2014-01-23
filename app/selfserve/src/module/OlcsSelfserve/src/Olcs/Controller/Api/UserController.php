<?php
/**
 * User Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class UserController extends AbstractRestfulController
{
    public function getList()
    {
        $userService = $this->getServiceLocator()->get('UserServiceFactory'); 

        $data = array(
            'rows' => $userService->getUsersData(),
        );

        return new JsonModel($data);
    }
}
