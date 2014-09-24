<?php

/**
 * Revoke Controller Trait
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\Mvc\MvcEvent;

/**
 * Revoke Controller Trait
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
trait RevokeControllerTrait
{

    protected $revokeBundle = array(
        'children' => array(
            'reasons' => array(
                'properties' => 'ALL'
            ),
            'presidingTc' => array(
                'properties' => 'ALL'
            ),
            'case' => array(
                'properties' => 'ALL'
            )
        )
    );

    public function getRevokes($caseId)
    {
        return $this->getRevokeBy('case', $caseId);
    }

    public function getRevoke($revokeId)
    {
        return $this->getRevokeBy('id', $revokeId);
    }

    /**
     * Abstracted away the rest call
     *
     * @param string $by
     * @param mixed $value
     * @return array
     */
    private function getRevokeBy($by, $value)
    {
        return $this->makeRestCall('ProposeToRevoke', 'GET', array($by => $value), $this->revokeBundle);
    }


    public function getPiReasonsNvpArray($goodsOrPsv, $niFlag)
    {
        $reasons = $this->makeRestCall(
            'Reason',
            'GET',
            [
                'isProposeToRevoke' => 1,
                'goodsOrPsv' => $goodsOrPsv,
                'isNi' => ($niFlag == 'Y') ? 0 : 0,
                'limit' => 'all'
            ]
        );

        $piReasons = [];

        foreach ($reasons['Results'] as $result) {
            $piReasons[$result['id']] = mb_substr($result['sectionCode'] . ' - ' . $result['description'], 0, 150);
        }

        return $piReasons;
    }

    public function getPresidingTcArray()
    {
        $tc = [];
        $reasons = $this->makeRestCall('PresidingTc', 'GET', []);
        foreach ($reasons['Results'] as $result) {
            $tc[$result['id']] = $result['name'];
        }

        return $tc;
    }
}
