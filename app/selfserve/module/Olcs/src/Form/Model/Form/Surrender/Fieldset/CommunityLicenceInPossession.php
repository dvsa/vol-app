<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("community-licence-in-possession")
 */
class CommunityLicenceInPossession
{
    /**
     * @Form\Options({
     *      "label": "licence.surrender.community_licence.possession.note",
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceInPossession";
}
