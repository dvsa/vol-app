<?php

/**
 * Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\Controller\Traits\Lva;

/**
 * Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends AbstractApplicationController
{
    use Lva\BusinessTypeTrait;

    /**
     * Business type section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $orgId = $this->getCurrentOrganisationId();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $this->formatDataForForm($this->getEntityService('Organisation')->getType($orgId));
        }

        $form = $this->getBusinessTypeForm()->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $this->getEntityService('Organisation')->save($this->formatDataForSave($orgId, $data));

            return $this->completeSection('business_type');
        }

        return $this->render('business_type', $form);
    }
}
