<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;

class AlertedShipsDetection implements AlertedShipsDetectionInterface
{
    public function __construct(
        private ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
    }

    public function getAlertedShipsOnLocation(ShipInterface $incomingShip): Collection
    {
        return $incomingShip->getCurrentMapField()->getShips()
            ->filter(
                fn (ShipInterface $ship) => !$ship->getUser()->isVacationRequestOldEnough()
                    && $ship->getUser() !== $incomingShip->getUser()
                    && ($ship->isFleetLeader() || $ship->getFleet() === null)
                    && !$ship->isAlertGreen()
                    && !$ship->isWarped()
                    && !$ship->getCloakState()
            )
            ->map(fn (ShipInterface $ship) => $this->shipWrapperFactory->wrapShip($ship));
    }
}
