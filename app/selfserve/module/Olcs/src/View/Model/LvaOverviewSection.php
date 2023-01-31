<?php

namespace Olcs\View\Model;

use Common\RefData;
use Laminas\View\Model\ViewModel;

abstract class LvaOverviewSection extends ViewModel
{
    protected $variables = array(
        'enabled' => true
    );

    /**
     * Holds the section reference
     *
     * @var string
     */
    private $ref;

    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'partials/overview_section';

    protected $type;

    public function __construct($ref, $data, $mode)
    {
        $this->ref = $ref;

        $this->setVariable('identifier', $data['id']);
        $this->setVariable('identifierIndex', $data['idIndex']);
        $this->setVariable('name', $this->getSectionName($ref, $data));
        $this->setVariable('route', 'lva-' . $this->type . '/' . $ref);
        $this->setVariable('link', 'section.link.' . $mode . '.' . $ref);
        $this->setVariable('anchorRef', $ref);
    }

    /**
     * Get section name
     *
     * @param string $ref
     * @param array $data
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
                $orgType = isset($data['licence']['organisation']['type']['id']) ?
                    $data['licence']['organisation']['type']['id'] : $data['organisation']['type']['id'];

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
