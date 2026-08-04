<?php

namespace Common\Service\Table\Formatter;

/**
 * Vehicle Disc No
 */
class VehicleDiscNo implements FormatterPluginManagerInterface
{
    private const PENDING = 'Pending';

    /**
     * Format Goods disc no
     *
     * @param array $data   Date
     * @param array $column Column data
     *
     * @return string '', 'Pending' or a Disc no
     */
    #[\Override]
    public function format($data, $column = [])
    {
        // if no specified date AND no removal date, then pending
        if (empty($data['specifiedDate']) && empty($data['removalDate'])) {
            return self::PENDING;
        }

        // if has some goods discs
        if (isset($data['goodsDiscs']) && is_array($data['goodsDiscs'])) {
            // get the latest disc
            $newestDisc = $this->getNewestDisc($data['goodsDiscs']);

            // if not ceased
            if (empty($newestDisc['ceasedDate'])) {
                // if has a disc no
                if (!empty($newestDisc['discNo'])) {
                    return $newestDisc['discNo'];
                }

                return self::PENDING;
            }
        }

        return '';
    }

    /**
     * Get the newest Goods disc
     *
     * @param array $discs array of all goods discs
     *
     * @return array Newist disc array data
     */
    private function getNewestDisc(array $discs)
    {
        $latestDisc = null;
        foreach ($discs as $disc) {
            if ($latestDisc === null || $disc['id'] > $latestDisc['id']) {
                $latestDisc = $disc;
            }
        }

        return $latestDisc;
    }
}
