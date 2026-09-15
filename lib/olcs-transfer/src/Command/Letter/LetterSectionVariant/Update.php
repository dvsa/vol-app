<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterSectionVariant;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/letter/letter-section-variant/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":50})
     */
    protected $goodsOrPsv;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isVariation;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isNi;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":50})
     */
    protected $organisationType;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $letterChoice;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $defaultContent;

    /**
     * @return string
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * @return bool
     */
    public function getIsVariation()
    {
        return $this->isVariation;
    }

    /**
     * @return bool
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * @return string
     */
    public function getOrganisationType()
    {
        return $this->organisationType;
    }

    /**
     * @return int
     */
    public function getLetterChoice()
    {
        return $this->letterChoice;
    }

    /**
     * @return array
     */
    public function getDefaultContent()
    {
        return $this->defaultContent;
    }
}
