<?php

/**
 * Form View Model
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\View\Model;

use Common\View\AbstractViewModel;
use Laminas\Form\Form as LaminasForm;

/**
 * Form View Model
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class Form extends AbstractViewModel
{
    /**
     * Set the template for the dashboard
     *
     * @var string
     */
    protected $template = 'form';

    /**
     * Set the form into the view.
     *
     * @param LaminasForm $form
     * @return void
     */
    public function setForm(LaminasForm $form)
    {
        $this->setVariable('form', $form);
    }
}
