<?php

/**
 * People LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Service\Lva;

use Common\Service\Entity\OrganisationEntityService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Form\Form;

/**
 * People LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PeopleLvaService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function lockPersonForm(Form $form, $orgType)
    {
        $fieldset = $form->get('data');
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        foreach (['title', 'forename', 'familyName', 'otherName', 'birthDate', 'position'] as $field) {
            if ($fieldset->has($field)) {
                $formHelper->lockElement(
                    $fieldset->get($field),
                    'people.' . $orgType . '.' . $field . '.locked'
                );
                $formHelper->disableElement($form, 'data->' . $field);
            }
        }

        if ($orgType !== OrganisationEntityService::ORG_TYPE_SOLE_TRADER) {
            $formHelper->remove($form, 'form-actions->submit');
        }
    }

    public function lockPartnershipForm(Form $form, $table)
    {
        $table->removeActions();
        $table->removeColumn('select');
    }

    public function lockOrganisationForm(Form $form, $table)
    {
        $table->removeActions();
        $table->removeColumn('select');
    }
}
