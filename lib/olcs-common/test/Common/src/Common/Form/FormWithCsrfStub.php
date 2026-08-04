<?php

declare(strict_types=1);

namespace CommonTest\Common\Form;

use Common\Form\Form;
use Common\Form\FormWithCsrfInterface;
use Common\Form\FormWithCsrfTrait;

/**
 * @template TFilteredValues $sut
 * @extends Form<TFilteredValues> $sut
 */
class FormWithCsrfStub extends Form implements FormWithCsrfInterface
{
    use FormWithCsrfTrait;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->initialiseCsrf();
    }
}
