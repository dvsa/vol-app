<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/letter/master-template/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;


    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $name;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Escape(false)
     */
    protected $templateContent;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isDefault;

    /**
     * Locale / chrome variant key. Extended vocabulary supports values beyond strict
     * ISO codes, e.g. en_GB, en_NI, cy_GB, customN_GB (VOL-7305).
     *
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":20})
     */
    protected $locale;

    /**
     * EditorJS JSON for the top-left header slot (VOL-7305).
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $headerLeftContent;

    /**
     * EditorJS JSON for the top-right header slot (VOL-7305).
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $headerRightContent;

    /**
     * EditorJS JSON for the signoff slot (VOL-7305).
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $signoffContent;

    /**
     * EditorJS JSON for the footer slot (VOL-7305).
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $footerContent;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTemplateContent()
    {
        return $this->templateContent;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return array
     */
    public function getHeaderLeftContent()
    {
        return $this->headerLeftContent;
    }

    /**
     * @return array
     */
    public function getHeaderRightContent()
    {
        return $this->headerRightContent;
    }

    /**
     * @return array
     */
    public function getSignoffContent()
    {
        return $this->signoffContent;
    }

    /**
     * @return array
     */
    public function getFooterContent()
    {
        return $this->footerContent;
    }
}
