<?php

/**
 * Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessTypeController extends AbstractApplicationController
{
    /**
     * Business type section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $orgId = $this->getCurrentOrganisation()['id'];

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $orgData = $this->getEntityService('Organisation')->getType($orgId);
            $data = array(
                'version' => $orgData['version'],
                'data' => array(
                    'type' => $orgData['type']['id']
                )
            );
        }

        $form = $this->getHelperService('FormHelper')
            ->createForm('Lva\BusinessType')
            ->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $data = array(
                'id' => $orgId,
                'version' => $data['version'],
                'type' => $data['data']['type']
            );
            $this->getEntityService('Organisation')->save($data);

            return $this->completeSection('business_type');
        }

        return new Section(
            [
                'title' => 'Business type',
                'form' => $form
            ]
        );
    }
}
