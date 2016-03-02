<?php

/**
 * Stored Cards Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Cpms\StoredCardList;

/**
 * Stored Cards Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait StoredCardsTrait
{
    /**
     * Setup the stored cards form element
     *
     * @param \Common\Form\Form $form
     */
    protected function setupSelectStoredCards(\Common\Form\Form $form)
    {
        $options = [];
        $response = $this->handleQuery(StoredCardList::create([]));
        if ($response->isOk()) {
            foreach ($response->getResult()['results'] as $storedCard) {
                $options[$storedCard['cardReference']] = $storedCard['cardScheme'] .' '. $storedCard['maskedPan'];
            }
        }

        if (empty($options)) {
            // if no stored cards then hide the select element
            $form->get('storedCards')->remove('card');
        } else {
            asort($options);
            array_unshift($options, 'form.fee-stored-cards.option1');
            $form->get('storedCards')->get('card')->setValueOptions($options);
        }
    }
}
