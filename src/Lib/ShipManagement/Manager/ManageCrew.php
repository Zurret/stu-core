<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use Override;
use RuntimeException;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;

class ManageCrew implements ManagerInterface
{
    public function __construct(
        private ShipCrewCalculatorInterface $shipCrewCalculator,
        private ShipSystemManagerInterface $shipSystemManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private ShipShutdownInterface $shipShutdown,
        private ShipLeaverInterface $shipLeaver,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function manage(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];
        $informations = new InformationWrapper();

        $newCrewCountArray = $values['crew'] ?? null;
        if ($newCrewCountArray === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();
        $user = $managerProvider->getUser();
        $buildplan = $ship->getBuildplan();

        if (
            isset($newCrewCountArray[$ship->getId()])
            && $ship->canMan()
            && $buildplan !== null
            && $ship->getUser() === $user
        ) {
            $newCrewCount = (int)$newCrewCountArray[$ship->getId()];
            if ($ship->getCrewCount() !== $newCrewCount) {
                $this->setNewCrew($newCrewCount, $wrapper, $buildplan, $managerProvider, $msg, $informations);
            }
        }

        return array_merge($msg, $informations->getInformations());
    }

    /**
     * @param array<string> $msg
     */
    private function setNewCrew(
        int $newCrewCount,
        ShipWrapperInterface $wrapper,
        ShipBuildplanInterface $buildplan,
        ManagerProviderInterface $managerProvider,
        array &$msg,
        InformationWrapper $informations
    ): void {
        $ship = $wrapper->get();

        if ($newCrewCount > $ship->getCrewCount()) {
            $this->increaseCrew($newCrewCount, $wrapper, $buildplan, $managerProvider, $msg, $informations);
        } else {
            $this->descreaseCrew($newCrewCount, $wrapper, $managerProvider, $msg);
        }
    }

    /**
     * @param array<string> $msg
     */
    private function increaseCrew(
        int $newCrewCount,
        ShipWrapperInterface $wrapper,
        ShipBuildplanInterface $buildplan,
        ManagerProviderInterface $managerProvider,
        array &$msg,
        InformationWrapper $informations
    ): void {
        $ship = $wrapper->get();

        if ($managerProvider->getFreeCrewAmount() == 0) {
            $msg[] = sprintf(
                _('%s: Keine Crew auf der %s vorhanden'),
                $ship->getName(),
                $managerProvider->getName(),
                $buildplan->getCrew()
            );
        } else {
            $additionalCrew = min(
                $newCrewCount - $ship->getCrewCount(),
                $managerProvider->getFreeCrewAmount()
            );

            if ($ship->getBuildplan() != null) {
                $mincrew = $ship->getBuildplan()->getCrew();
                $actualcrew = $ship->getCrewCount();
                $maxcrew = $this->shipCrewCalculator->getMaxCrewCountByRump($ship->getRump());

                if (
                    $actualcrew >= $mincrew
                    && $actualcrew + $additionalCrew > $maxcrew
                    && ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS) && ($additionalCrew > 0
                        && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() === ShipSystemModeEnum::MODE_OFF
                        && !$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $informations)))
                ) {

                    $additionalCrew  = 0;
                }
            }

            $managerProvider->addShipCrew($ship, $additionalCrew);
            $msg[] = sprintf(
                _('%s: %d Crewman wurde(n) hochgebeamt'),
                $ship->getName(),
                $additionalCrew
            );

            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
                $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
            }
        }
    }

    /**
     * @param array<string> $msg
     */
    private function descreaseCrew(
        int $newCrewCount,
        ShipWrapperInterface $wrapper,
        ManagerProviderInterface $managerProvider,
        array &$msg
    ): void {
        $ship = $wrapper->get();
        $user = $managerProvider->getUser();

        if ($managerProvider->getFreeCrewStorage() == 0) {
            $msg[] = sprintf(
                _('%s: Kein Platz für die Crew auf der %s'),
                $ship->getName(),
                $managerProvider->getName()
            );
            return;
        }

        $ownCrewCount = $this->troopTransferUtility->ownCrewOnTarget($user, $ship);

        $removedCrew = min(
            $ownCrewCount,
            $ship->getCrewCount() - $newCrewCount,
            $managerProvider->getFreeCrewStorage()
        );

        $this->dumpForeignCrew($ship);

        $managerProvider->addCrewAssignments(array_slice(
            $ship->getCrewAssignments()->toArray(),
            0,
            $removedCrew
        ));

        $msg[] = sprintf(
            _('%s: %d Crewman wurde(n) runtergebeamt'),
            $ship->getName(),
            $removedCrew
        );

        if ($removedCrew === $ownCrewCount) {
            $this->shipShutdown->shutdown($wrapper);
        }
    }

    private function dumpForeignCrew(ShipInterface $ship): void
    {
        foreach ($ship->getCrewAssignments() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() !== $ship->getUser()) {
                $this->shipLeaver->dumpCrewman(
                    $shipCrew,
                    sprintf(
                        'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                        $shipCrew->getCrew()->getName(),
                        $ship->getName(),
                        $ship->getUser()->getName(),
                    )
                );
            }
        }
    }
}
