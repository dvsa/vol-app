<?php

namespace Common\Service\Qa;

use Common\Form\QaForm;

interface DataHandlerInterface
{
    /**
     * Set the form data and make any required modifications to the form elements
     */
    public function setData(QaForm $form);
}
