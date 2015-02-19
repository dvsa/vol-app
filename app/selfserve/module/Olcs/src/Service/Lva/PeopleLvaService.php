<?php

/**
 * Poeople LVA service
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

    public function lockPersonForm(Form $form, $hideSubmit = false)
    {
        $fieldset = $form->get('data');
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        foreach (['title', 'forename', 'familyName', 'otherName', 'birthDate'] as $field) {
            $formHelper->lockElement(
                $fieldset->get($field),
                'people.' . $field . '.locked'
            );
            $formHelper->disableElement($form, 'data->' . $field);
        }

        if ($hideSubmit) {
            $formHelper->remove($form, 'form-actions->submit');
        }
    }

    public function lockPartnershipForm(Form $form, $table)
    {
        $table->removeActions();
        $table->removeColumn('select');
    }

    public function lockOrganisationForm(Form $form, $table, $orgId)
    {
    }
}
