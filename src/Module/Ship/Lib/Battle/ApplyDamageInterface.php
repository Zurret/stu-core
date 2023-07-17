<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\DamageWrapper;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface ApplyDamageInterface
{
    public function damage(
        DamageWrapper $damage_wrapper,
        ShipWrapperInterface $shipWrapper
    ): InformationWrapper;

    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper;

    public function damageShipSystem(
        ShipWrapperInterface $wrapper,
        $system,
        $dmg,
        InformationWrapper $informationWrapper
    ): bool;
}
