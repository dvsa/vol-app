<?php

/**
 * Venue Address formatter
 */

namespace Common\Service\Table\Formatter;

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
            return $data['venue']['name'] . ' - ' . $this->addressFormatter->format($data['venue']['address'], ['addressFields' => 'FULL']);
        }
        if (!empty($data['venueOther'])) {
            // other venue
            return $data['venueOther'];
        }

        return '';
    }
}
