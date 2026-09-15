<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Prohibition;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/prohibition")
 * @Transfer\Method("POST")
 */
class Create extends AbstractCommand implements
    FieldType\CasesInterface
{
    use FieldType\Traits\Cases;
    use FieldType\Traits\ProhibitionType;

    // Fields
    use FieldType\Traits\Vrm;

    /**
     * @var int
     *
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\DateNotInFuture")
     */
    public $prohibitionDate = null;

    /**
     * @var String
     *
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    public $isTrailer = null;

    /**
     * @var string
     *
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\DateNotInFuture")
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\DateCompare",
     *     options={
     *         "compare_to":"prohibitionDate",
     *         "compare_to_label":"Prohibition date",
     *         "operator": "gte",
     *     }
     * )
     */
    public $clearedDate = null;

    /**
     * @var string
     *
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $imposedAt = null;

    /**
     * @return mixed
     */
    public function getProhibitionDate()
    {
        return $this->prohibitionDate;
    }

    /**
     * @return mixed
     */
    public function getIsTrailer()
    {
        return $this->isTrailer;
    }

    /**
     * @return mixed
     */
    public function getClearedDate()
    {
        return $this->clearedDate;
    }

    /**
     * @return mixed
     */
    public function getImposedAt()
    {
        return $this->imposedAt;
    }
}
