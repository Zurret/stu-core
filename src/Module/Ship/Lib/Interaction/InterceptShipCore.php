<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class InterceptShipCore implements InterceptShipCoreInterface
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ShipSystemManagerInterface $shipSystemManager,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Override]
    public function intercept(ShipInterface $ship, ShipInterface $target, InformationInterface $informations): void
    {
        $userId = $ship->getUser()->getId();
        $wrapper = $this->shipWrapperFactory->wrapShip($ship);
        $targetWrapper = $this->shipWrapperFactory->wrapShip($target);

        if ($ship->getDockedTo() !== null) {
            $informations->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }
        if ($target->getFleet() !== null) {
            foreach ($target->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($this->shipWrapperFactory->wrapShip($fleetShip), ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException) {
                }
                $this->shipRepository->save($fleetShip);
            }

            $informations->addInformationf("Die Flotte %s wurde abgefangen", $target->getFleet()->getName());
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $this->shipSystemManager->deactivate($targetWrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

            $informations->addInformationf("Die %s wurde abgefangen", $target->getName());
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";

            $this->shipRepository->save($target);
        }

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId());

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $href
        );
        $interceptorLeftWarp = false;
        if ($ship->getFleet() !== null) {
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($this->shipWrapperFactory->wrapShip($fleetShip), ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                    $interceptorLeftWarp = true;
                } catch (AlreadyOffException) {
                }
                $this->shipRepository->save($fleetShip);
            }
        } else {
            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                $interceptorLeftWarp = true;
            } catch (AlreadyOffException) {
            }

            $this->shipRepository->save($ship);
        }
        $this->entityManager->flush();

        //Alert red check for the target(s)
        $this->alertReactionFacade->doItAll($targetWrapper, $informations);

        //Alert red check for the interceptor(s)
        if ($interceptorLeftWarp) {
            $this->alertReactionFacade->doItAll($wrapper, $informations);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
