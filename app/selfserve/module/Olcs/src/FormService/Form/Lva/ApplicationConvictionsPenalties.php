<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\ConvictionsPenalties;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application convictions and penalties
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationConvictionsPenalties extends ConvictionsPenalties
{
    use ButtonsAlterations;

    /**
     * Make form alterations
     *
     * @param Form  $form   form
     * @param array $params parameters
     *
     * @return Form
     */
    protected function alterForm($form, array $params = [])
    {
        parent::alterForm($form, $params);
        $this->alterButtons($form);

        return $form;
    }
}
