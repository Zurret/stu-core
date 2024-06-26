<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;

interface ReloadMinimalEpsInterface
{
    public function reload(FleetWrapperInterface $fleetWrapper, int $minimalPercentage = 20): void;
}
