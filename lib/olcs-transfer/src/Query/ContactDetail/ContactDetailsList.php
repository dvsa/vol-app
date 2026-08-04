<?php

namespace Dvsa\Olcs\Transfer\Query\ContactDetail;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/contact-details")
 */
class ContactDetailsList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTraitOptional;
    use OrderedTrait;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToLower")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "ct_complainant", "ct_corr", "ct_driver", "ct_est", "ct_hackney", "ct_irfo_op",
     *              "ct_obj", "ct_partner", "ct_reg", "ct_rep", "ct_requestor", "ct_ta", "ct_tcon",
     *              "ct_team_user", "ct_tm", "ct_user", "ct_work"
     *          }
     *      }
     * )
     * @Transfer\Optional
     */
    protected $contactType;

    /**
     * Returns contact type
     *
     * @return int
     */
    public function getContactType()
    {
        return $this->contactType;
    }
}
