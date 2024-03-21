<?php

namespace Olcs\Service\Marker;

/**
 * MarkerInterface
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
abstract class AbstractMarker implements MarkerInterface
{
    /**
     * @var \Laminas\View\Helper\Partial
     */
    private $partialHelper;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Render a marker partial
     *
     * @param string $template  The partial name, this will be postfixed onto "partials/marker/"
     * @param array  $variables Partial variable
     *
     * @return string
     */
    protected function renderPartial($template, array $variables = [])
    {
        $partialHelper = $this->getPartialHelper();

        return $partialHelper('marker/' . $template, $variables);
    }

    /**
     * @return \Laminas\View\Helper\Partial
     */
    protected function getPartialHelper()
    {
        return $this->partialHelper;
    }

    /**
     * @param \Laminas\View\Helper\Partial $partialHelper
     *
     * @return AbstractMarker
     */
    public function setPartialHelper(\Laminas\View\Helper\Partial $partialHelper)
    {
        $this->partialHelper = $partialHelper;
        return $this;
    }

    /**
     * Get data used to populate markers
     *
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * Set data used to populate markers
     *
     * @param array $data
     *
     * @return AbstractMarker
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
