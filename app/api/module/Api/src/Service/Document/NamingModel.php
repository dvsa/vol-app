<?php

/**
 * Document Naming Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Document Naming Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NamingModel
{
    /**
     * @param string $description
     * @param string $extension
     */
    public function __construct(private readonly DateTime $date, private $description, private $extension, private readonly ?\Dvsa\Olcs\Api\Entity\System\Category $category = null, private readonly ?\Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory = null, private readonly ?\Dvsa\Olcs\Api\Service\Document\ContextProviderInterface $entity = null)
    {
    }

    /**
     * @param $flag
     * @return string
     */
    public function getDate($flag)
    {
        /*
         * DateTime return zeros as a microseconds so we need to do the trick
         */
        if (!empty($flag) && str_contains((string) $flag, 'u')) {
            [$usec, $sec] = explode(' ', microtime());
            $usec = substr($usec, 2, 6);
            $date = $this->date->format($flag);
            return str_replace('000000', $usec, $date);
        }
        return $this->date->format($flag);
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        if ($this->category === null) {
            return null;
        }

        return $this->category->getDescription();
    }

    /**
     * @return string
     */
    public function getSubCategory()
    {
        if ($this->subCategory === null) {
            return null;
        }

        return $this->subCategory->getSubCategoryName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        if ($this->entity === null) {
            return '';
        }

        return $this->entity->getContextValue();
    }
}
