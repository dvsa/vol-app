<?php

namespace Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\MapperInterface;

class CurrentDiscs implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        $inPossession = isset($data['discDestroyed']) ? 'Y' : 'N';
        $lost = isset($data['discLost']) ? 'Y' : (isset($data['discLostInfo']) ? 'Y' : 'N');
        $stolen = isset($data['discStolen']) ? 'Y' : (isset($data['discStolenInfo']) ? 'Y' : 'N');

        return [
            'version' => $data['version'],
            'possessionSection' => [
                'inPossession' => $inPossession,
                'info' => [
                    'number' => $data['discDestroyed'] ?? null
                ]
            ],
            'lostSection' => [
                'lost' => $lost,
                'info' => [
                    'number' => $data['discLost'] ?? null,
                    'details' => $data['discLostInfo'] ?? null
                ]
            ],
            'stolenSection' => [
                'stolen' => $stolen,
                'info' => [
                    'number' => $data['discStolen'] ?? null,
                    'details' => $data['discStolenInfo'] ?? null
                ]
            ]
        ];
    }

    public static function mapFromForm(array $data): array
    {
        $inPossession = $data['possessionSection']['inPossession'] == "Y";
        $possessionData = $data['possessionSection']['info'];
        $lost = $data['lostSection']['lost'] == "Y";
        $stolen = $data['stolenSection']['stolen'] == "Y";

        $return = [];
        $self = new self();
        $return['discDestroyed'] = $inPossession && !empty($possessionData['number']) ? $possessionData['number'] : null;

        if ($lost) {
            $lostData = $self->getLostInfo($data['lostSection']['info']);
            $return = array_merge($return, $lostData);
        } else {
            $return['discLost'] = null;
            $return['discLostInfo'] = null;
        }

        if ($stolen) {
            $stolenData = $self->getStolenInfo($data['stolenSection']['info']);
            $return = array_merge($return, $stolenData);
        } else {
            $return['discStolen'] = null;
            $return['discStolenInfo'] = null;
        }

        return $return;
    }

    public function getLostInfo(array $section): array
    {
        $return = [];
        if (!empty($section['number'])) {
            $return['discLost'] = $section['number'];
        }

        if (!empty($section['details'])) {
            $return['discLostInfo'] = $section['details'];
        }

        return $return;
    }

    public function getStolenInfo(array $section): array
    {
        $return = [];
        if (!empty($section['number'])) {
            $return['discStolen'] = $section['number'];
        }

        if (!empty($section['details'])) {
            $return['discStolenInfo'] = $section['details'];
        }

        return $return;
    }
}
