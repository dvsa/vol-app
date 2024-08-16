<?php

namespace Olcs\View\Model;

use Common\RefData;
use Laminas\View\Model\ViewModel;

abstract class LvaOverviewSection extends ViewModel
{
    protected $variables = [
        'enabled' => true
    ];

    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'partials/overview_section';

    protected $type;

    /**
     * @param string $ref
     */
    public function __construct(
        private $ref,
        $data,
        $mode
    ) {
        $this->setVariable('identifier', $data['id']);
        $this->setVariable('identifierIndex', $data['idIndex']);
        $this->setVariable('name', $this->getSectionName($this->ref, $data));
        $this->setVariable('route', 'lva-' . $this->type . '/' . $this->ref);
        $this->setVariable('link', 'section.link.' . $mode . '.' . $this->ref);
        $this->setVariable('anchorRef', $this->ref);
    }

    /**
     * Get section name
     *
     *
     * @return string
     */
    private function getSectionName(string $ref, array $data): string
    {
        // default section name
        $sectionName = 'section.name.' . $ref;

        switch ($ref) {
            case 'people':
                // change the section name based on org type
                $orgType = $data['licence']['organisation']['type']['id'] ?? $data['organisation']['type']['id'];

                $sectionName .= '.' . $orgType;
                break;
            case 'operating_centres':
                // change the section name if it is LGV only
                if (isset($data['vehicleType']['id']) && (RefData::APP_VEHICLE_TYPE_LGV === $data['vehicleType']['id'])) {
                    $sectionName .= '.lgv';
                }
                break;
        }

        return $sectionName;
    }
}
