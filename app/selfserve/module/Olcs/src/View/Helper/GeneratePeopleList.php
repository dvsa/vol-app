<?php

/**
 * Generate People List
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Generate People List. Takes a label and array of people and returns array structure compatible with readonly
 * main views
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class GeneratePeopleList extends AbstractHelper
{
    public function __invoke($people = [], $label = '')
    {
        return $this->render($people, $label);
    }

    public function render($people = [], $labelKey = '')
    {
        if (!empty($people)) {
            for ($i = 0; $i < count($people); $i++) {
                $label = ($i == 0) ? $labelKey : '';
                $peopleList[] = [
                    'label' => $label,
                    'value' =>
                        $people[$i]['person']['forename'] . ' ' .
                        $people[$i]['person']['familyName']
                ];
            }
        } else {
            $peopleList[] = [
                'label' => $labelKey,
                'value' => ''
            ];
        }

        return $peopleList;
    }
}
