<?php

/**
 * Inspection Request Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Zend\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Inspection Request Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait InspectionRequestTrait
{
    public function addAction()
    {
        $this->placeholder()->setPlaceholder('enforcementAreaName', $this->getEnforcementAreaName());

        if (!$this->enforcementAreaName) {
            $this->getServiceLocator()
                ->get('Helper\FlashMessenger')
                ->addErrorMessage('internal-inspection-request.area-not-set');

            return $this->redirectToIndex();
        }

        $this->setUpOcListbox();

        return parent::add(
            $this->formClass,
            new AddFormDefaultData($this->defaultData),
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            'internal-inspection-request-inspection-request-added',
            $this->addContentTitle
        );
    }

    public function editAction()
    {
        $this->placeholder()->setPlaceholder('enforcementAreaName', $this->getEnforcementAreaName());

        $this->setUpOcListbox();

        return $this->edit(
            $this->formClass,
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->updateCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            'internal-inspection-request-inspection-request-updated',
            $this->editContentTitle
        );
    }

    public function alterFormForEdit($form, $formData)
    {
        if (isset($formData['data']['enforcementAreaName'])) {
            $this->placeholder()->setPlaceholder(
                'enforcementAreaName',
                $formData['data']['enforcementAreaName']
            );
        }
        return $form;
    }
}
