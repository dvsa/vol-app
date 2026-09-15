<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("community-licences-data-annul")
 */
class CommunityLicencesDataAnnul
{
    /**
     * @Form\Attributes({"value": "internal.community_licence.confirm_annul_licences"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $confirm;
}
