<?php

namespace Dvsa\Olcs\Transfer\Query;

trait OrderedTrait
{
    /**
     * The field to sort by - must not be empty.
     *
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\NotEmpty")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Sort")
     */
    protected $sort;

    /**
     * Can only be one of ASC or DESC in upper case.
     *
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToUpper")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Order")
     */
    protected $order;

    /**
     * Set this property in you constructor to only enable specified values for $sort property
     *
     * @var array
     * @Transfer\DoNotExchange
     * @Transfer\Optional
     */
    protected $sortWhitelist = [];

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     * @return void
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return void
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return array
     */
    public function getSortWhitelist()
    {
        return $this->sortWhitelist;
    }

    /**
     * @param array $sortWhitelist
     * @return void
     */
    public function setSortWhitelist($sortWhitelist)
    {
        $this->sortWhitelist = $sortWhitelist;
    }

    /**
     * @return bool
     */
    public function isSortWhitelisted()
    {
        if (empty($this->sortWhitelist)) {
            return true;
        }

        // Strict, so a whitelist entry cannot be matched by type juggling rather than by
        // being the value it says it is.
        //
        // Note for whoever populates the first whitelist: $sort may be a comma-separated
        // list of fields, because Validators\Sort permits commas, and this compares the
        // whole joined string against the whitelist. A multi-field sort will therefore fail
        // even when every individual field is listed. Split on commas and check each field
        // before relying on this.
        if (!in_array($this->sort, $this->sortWhitelist, true)) {
            return false;
        }

        return true;
    }
}
