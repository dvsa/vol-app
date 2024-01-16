<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

class BusNoticePeriod
{
    /**
     * @Form\Options({"label": "Rules"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium"})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":70})
     */
    public $noticeArea = null;

    /**
     * @Form\Options({"label": "Notice period"})
     * @Form\Required(true)
     * @Form\Type("number")
     * @Form\Attributes({"class":"small"})
     * @Form\Filter({"name": "Digits"})
     * @Form\Validator("Between", options={"min":0, "max":999})
     */
    public $standardPeriod = null;
}
