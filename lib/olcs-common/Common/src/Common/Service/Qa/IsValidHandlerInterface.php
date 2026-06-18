<?php

namespace Common\Service\Qa;

use Common\Form\QaForm;

interface IsValidHandlerInterface
{
    /**
     * Perform any form validation operations specific to this custom type
     *
     *
     * @return bool
     */
    public function isValid(QaForm $form);
}
