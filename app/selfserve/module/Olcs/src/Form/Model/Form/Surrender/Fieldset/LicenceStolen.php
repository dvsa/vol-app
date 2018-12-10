<?php
/**
 * Created by PhpStorm.
 * User: parthvyas
 * Date: 06/12/2018
 * Time: 14:54
 */

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-stolen")
 */
class LicenceStolen
{
    /**
     * @Form\Attributes({
     *     "value":"licence.surrender.licence.stolen.note"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceStolen";

    /**
     * @Form\Type("\Zend\Form\Element\Textarea")
     */
    public $details = null;
}