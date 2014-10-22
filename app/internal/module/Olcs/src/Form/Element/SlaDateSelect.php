<?php

/**
 * SlaDateSelect
 */
namespace Olcs\Form\Element;

use Zend\Form\Element\DateSelect as ZendDateSelect;
use Common\Service\Data\Sla as SlaService;
use Zend\Form\Form as ZendForm;

/**
 * SlaDateSelect
 */
class SlaDateSelect extends ZendDateSelect
{
    /**
     * @var SlaService
     */
    protected $slaService;

    /**
     *
     * @return \Common\Service\Data\Sla
     */
    public function getSlaService()
    {
        return $this->slaService;
    }

    /**
     *
     * @param SlaService $slaService
     * @return \Olcs\Form\Element\SlaDateSelect
     */
    public function setSlaService(SlaService $slaService)
    {
        $this->slaService = $slaService;
        return $this;
    }

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return mixed
     */
    public function prepareElement(FormInterface $form)
    {
        parent::prepareElement($form);

        // our bit
    }

    public function setHintFromSla(ZendForm $form)
    {
        $contextData = $form->getInputFilter()->getRawValues();

        try {
            $date = $this->getSlaService()->getTargetDate($this->getOption('category'), $this->getName(), $contextData);

            $hint = 'Target date: ' . date('d/m/Y', strtotime($date));
        } catch (\LogicException $e) {

            $hint = 'There was no target date found'
        }


        $this->setOption('hint', $value)
    }
}
