<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

interface TransportManagerSectionInterface
{
    public function sectionSerialize();

    public function populate(array $transportManagerApplication);
}
