<?php

namespace Dvsa\Olcs\Transfer\Query\DataRetention;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\UserOptional;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedTrait;

/**
 * @Transfer\RouteName("backend/data-retention/records")
 */
final class Records extends AbstractQuery implements
    PagedQueryInterface,
    OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use UserOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $dataRetentionRuleId;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "pending",
     *              "deferred"
     *          }
     *     }
     * )
     */
    protected $nextReview;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $markedForDeletion;

    /**
     * @var string|null
     * @Transfer\Optional
     */
    protected $assignedToUser;

    /**
     * @var string|null
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "lcat_gv",
     *              "lcat_psv"
     *          }
     *     }
     * )
     */
    protected $goodsOrPsv;

    /**
     * Get Data retention rule id
     *
     * @return int
     *
     */
    public function getDataRetentionRuleId()
    {
        return $this->dataRetentionRuleId;
    }

    /**
     * Get next review
     *
     * @return string
     *
     */
    public function getNextReview()
    {
        return $this->nextReview;
    }

    /**
     * Get marked for deletion
     *
     * @return string
     *
     */
    public function getMarkedForDeletion()
    {
        return $this->markedForDeletion;
    }

    /**
     * Get assigned to User
     *
     * @return string|null
     *
     */
    public function getAssignedToUser(): ?string
    {
        return $this->assignedToUser;
    }

    /**
     * @return string|null
     */
    public function getGoodsOrPsv(): ?string
    {
        return $this->goodsOrPsv;
    }
}
