<?php
/**
 * Created by PhpStorm.
 * User: shaunhare
 * Date: 2019-01-02
 * Time: 14:41
 */

namespace Olcs\Form\Model\Form\Surrender;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("community-licence")
 * @Form\Type("\Common\Form\Form")
 */
class CommunityLicence
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\Fieldset\CommunityLicenceDocument")
     */
    public $communityLicenceDocument = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButton")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions = null;
}
