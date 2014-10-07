<?php

/**
 * Section View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model;

use Zend\Form\Form;
use Common\View\AbstractViewModel;

/**
 * Section View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Section extends AbstractViewModel
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'section';

    public function setForm(Form $form)
    {
        $this->setVariable('form', $form);
    }
}
