<?php

/**
 * SlaDateTimeSelect
 */
namespace Olcs\Form\Element;

use Zend\Form\Element\DateTimeSelect as ZendDateTimeSelect;
use Common\Service\Data\Sla as SlaService;
use Zend\Form\Form as ZendForm;
use Zend\Form\FormInterface as ZendFormInterface;
use Common\Service\Data\SlaServiceAwareTrait as SlaServiceAwareTrait;

/**
 * SlaDateTimeSelect
 */
class SlaDateTimeSelect extends ZendDateTimeSelect
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

            $hint = 'Target date: ' . date('d/m/Y', strtotime($date));

        } catch (\LogicException $e) {

            $hint = 'There was no target date found';
        }

        $pattern = $this->getOption('pattern');
        if (!empty($pattern)) {
            if (strstr($pattern, '{{SLA_HINT}}')) {
                $pattern = str_replace('{{SLA_HINT}}', '<p class="hint">' . $hint . '</p>', $pattern);

                $this->setOption('pattern', $pattern);
            } elseif (strstr($pattern, '</div>')) {
                $pattern = str_replace('</div>', '<p class="hint">' . $hint . '</p></div>', $pattern);

                $this->setOption('pattern', $pattern);
            }
        } else {
            $this->setOption('hint', $hint);
        }
    }
}
