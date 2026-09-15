<?php

/**
 * CreateSeperatorSheet
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Scan;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\DateReceived;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/scan/separator-sheet")
 * @Transfer\Method("POST")
 */
final class CreateSeparatorSheet extends AbstractCommand
{
    use DateReceived;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $categoryId;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $subCategoryId;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $entityIdentifier;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $descriptionId;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $description;

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return int
     */
    public function getSubCategoryId()
    {
        return $this->subCategoryId;
    }

    /**
     * Get Entity Identifier, this could be an int entity ID or a string eg Licence Number
     *
     * @return int|string
     */
    public function getEntityIdentifier()
    {
        return $this->entityIdentifier;
    }

    /**
     * @return int
     */
    public function getDescriptionId()
    {
        return $this->descriptionId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
