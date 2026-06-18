<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

class HoursOfWork extends AbstractSection implements TransportManagerSectionInterface
{
    use SectionSerializeTrait;

    private $hoursMon;

    private $hoursTue;

    private $hoursWed;

    private $hoursThu;

    private $hoursFri;

    private $hoursSat;

    private $hoursSun;

    public function setHoursMon(mixed $hoursMon): void
    {
        $this->hoursMon = $hoursMon;
    }

    public function setHoursTue(mixed $hoursTue): void
    {
        $this->hoursTue = $hoursTue;
    }

    public function setHoursWed(mixed $hoursWed): void
    {
        $this->hoursWed = $hoursWed;
    }

    public function setHoursThu(mixed $hoursThu): void
    {
        $this->hoursThu = $hoursThu;
    }

    public function setHoursFri(mixed $hoursFri): void
    {
        $this->hoursFri = $hoursFri;
    }

    public function setHoursSat(mixed $hoursSat): void
    {
        $this->hoursSat = $hoursSat;
    }

    public function setHoursSun(mixed $hoursSun): void
    {
        $this->hoursSun = $hoursSun;
    }


    /**
     * @return static
     */
    #[\Override]
    public function populate(array $transportManagerApplication)
    {
        $properties = array_keys(get_object_vars($this));
        array_map(function ($v) use ($transportManagerApplication) {
            $this->{'set' . ucfirst($v)}($transportManagerApplication[$v]);
        }, $properties);

        return $this;
    }
}
