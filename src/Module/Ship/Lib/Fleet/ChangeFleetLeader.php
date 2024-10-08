<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Override;
use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ChangeFleetLeader implements ChangeFleetLeaderInterface
{
    public function __construct(private FleetRepositoryInterface $fleetRepository, private ShipRepositoryInterface $shipRepository, private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend)
    {
    }

    #[Override]
    public function change(ShipInterface $oldLeader): void
    {
        $fleet = $oldLeader->getFleet();
        if ($fleet === null) {
            throw new RuntimeException('no fleet available');
        }

        $newLeader = current(
            array_filter(
                $fleet->getShips()->toArray(),
                fn (ShipInterface $ship): bool => $ship !== $oldLeader
            )
        );

        if ($newLeader === false) {
            $this->cancelColonyBlockOrDefend->work(
                $oldLeader,
                new InformationWrapper()
            );
        } else {
            $newLeader->setIsFleetLeader(true);
            $this->shipRepository->save($newLeader);

            $fleet->setLeadShip($newLeader);
            $this->fleetRepository->save($fleet);
        }

        $fleet->getShips()->removeElement($oldLeader);

        $oldLeader->setFleet(null);
        $oldLeader->setIsFleetLeader(false);
        $this->shipRepository->save($oldLeader);

        if ($newLeader === false) {
            $this->fleetRepository->delete($fleet);
        }
    }
}
