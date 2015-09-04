<?php

namespace Olcs\Service\Marker;

/**
 * MarkerInterface
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
interface MarkerInterface
{
    public function canRender();

    public function render();

    public function setData(array $data);
}
