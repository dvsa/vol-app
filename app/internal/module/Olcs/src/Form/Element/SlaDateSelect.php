<?php

/**
 * SlaDateSelect
 */
namespace Olcs\Form\Element;

use Zend\Form\Element\DateSelect as ZendDateSelect;
use Common\Service\Data\Sla as SlaService;
use Zend\Form\Form as ZendForm;
use Zend\Form\FormInterface as ZendFormInterface;
use Common\Service\Data\SlaServiceAwareTrait as SlaServiceAwareTrait;

/**
 * SlaDateSelect
 */
class SlaDateSelect extends ZendDateSelect
{
    use SlaServiceAwareTrait;

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  ZendFormInterface $form
     * @return mixed
     */
    public function prepareElement(ZendFormInterface $form)
    {
        parent::prepareElement($form);

        $this->setHintFromSla($form);
    }

    /**
     *
     * @param ZendForm $form
     */
    public function setHintFromSla(ZendForm $form)
    {
        try {
            $date = $this->getSlaService()->getTargetDate($this->getOption('category'), $this->getOption('field'));

            if (null === $date) {
                throw new \LogicException('There was no target date found');
            }

            $hint = 'Target date: ' . date(\DATE_FORMAT, strtotime($date));

        } catch (\LogicException $e) {

            $hint = 'There was no target date found';
        }

        $this->setOption('hint', $hint);
    }
}
