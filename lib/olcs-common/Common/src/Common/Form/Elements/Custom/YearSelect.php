<?php

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;

class YearSelect extends LaminasElement\Select
{
    use Traits\YearDelta;

    /**
     * Min year to use for the select (default: current year - 100)
     *
     * @var int
     */
    protected $minYear;

    /**
     * Max year to use for the select (default: current year)
     *
     * @var int
     */
    protected $maxYear;

    /**
     * Constructor. Add two selects elements
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = [])
    {
        $this->minYear = date('Y') - 100;
        $this->maxYear = date('Y');

        parent::__construct($name, $options);
    }

    /**
     * @param  array $options
     * @return YearSelect
     */
    #[\Override]
    public function setOptions($options)
    {
        // set Min/Max Year based on element's options
        $minYearDelta = empty($options['min_year_delta']) ? null : $options['min_year_delta'];
        $maxYearDelta = empty($options['max_year_delta']) ? null : $options['max_year_delta'];
        $this->setMinMaxYear($minYearDelta, $maxYearDelta);

        $years = [];
        for ($i = $this->maxYear; $i >= $this->minYear; --$i) {
            $years[$i] = $i;
        }

        $options['options'] = $years;

        return parent::setOptions($options);
    }

    /**
     * @param int $minYear
     */
    public function setMinYear($minYear): static
    {
        $this->minYear = $minYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinYear()
    {
        return $this->minYear;
    }

    /**
     * @param int $maxYear
     */
    public function setMaxYear($maxYear): static
    {
        $this->maxYear = $maxYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxYear()
    {
        return $this->maxYear;
    }
}
