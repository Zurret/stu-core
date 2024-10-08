<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\FleetInterface;

interface FleetWrapperInterface
{
    public function get(): FleetInterface;

    public function getLeadWrapper(): ShipWrapperInterface;

    /**
     * @return ArrayCollection<int, ShipWrapperInterface>
     */
    public function getShipWrappers(): Collection;

    public function isForeignFleet(): bool;
}
