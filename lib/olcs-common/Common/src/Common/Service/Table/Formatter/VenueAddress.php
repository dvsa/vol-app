<?php

/**
 * Venue Address formatter
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Venue Address formatter
 */
class VenueAddress implements FormatterPluginManagerInterface
{
    public function __construct(private Address $addressFormatter)
    {
    }

    /**
     * Format a venue address
     *
     * @param  array $data
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!empty($data['venue'])) {
            // name and address
            // The address is another formatter's output and stays raw; escaping it here would
            // double-escape and is that formatter's job.
            return Escape::html($data['venue']['name'])
                . ' - '
                . $this->addressFormatter->format($data['venue']['address'], ['addressFields' => 'FULL']);
        }
        if (!empty($data['venueOther'])) {
            // other venue
            return Escape::html($data['venueOther']);
        }

        return '';
    }
}
