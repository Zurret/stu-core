<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use InvalidArgumentException;
use Override;
use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Colonize implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COLONIZE';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository, private BuildingRepositoryInterface $buildingRepository, private PlanetFieldRepositoryInterface $planetFieldRepository, private PlanetColonizationInterface $planetColonization, private ColonyRepositoryInterface $colonyRepository, private ColonyStorageManagerInterface $colonyStorageManager, private CommodityRepositoryInterface $commodityRepository, private ShipRemoverInterface $shipRemover, private InteractionCheckerInterface $interactionChecker, private ColonizationCheckerInterface $colonizationChecker, private TroopTransferUtilityInterface $troopTransferUtility, private ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository, private UserRepositoryInterface $userRepository, private ComponentLoaderInterface $componentLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = request::getIntFatal('colid');
        $fieldId = request::getIntFatal('field');

        $colony = $this->colonyRepository->find($colonyId);
        $field = $this->planetFieldRepository->find($fieldId);

        if ($field === null || $colony === null) {
            return;
        }

        if (!$ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            return;
        }

        if ($colony !== $field->getHost()) {
            return;
        }
        if (!$this->interactionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }
        if ($this->colonizationChecker->canColonize($user, $colony) === false) {
            return;
        }

        $shipRumpColonizationBuilding = $this->shipRumpColonizationBuildingRepository->findByShipRump($ship->getRump());
        if ($shipRumpColonizationBuilding === null) {
            return;
        }

        $building = $this->buildingRepository->find($shipRumpColonizationBuilding->getBuildingId());
        if ($building === null) {
            return;
        }

        if (!$user->hasColony()) {
            $user->setState(UserEnum::USER_STATE_ACTIVE);
            $this->userRepository->save($user);
            $this->planetColonization->colonize(
                $colony,
                $userId,
                $building,
                $field
            );
            $this->colonyStorageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeEnum::COMMODITY_BUILDING_MATERIALS),
                150
            );
            $this->colonyStorageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeEnum::COMMODITY_TRANSPARENT_ALUMINIUM),
                150
            );
            $this->colonyStorageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeEnum::COMMODITY_DURANIUM),
                150
            );
            $this->colonyStorageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeEnum::COMMODITY_DEUTERIUM),
                100
            );
        } else {

            $this->planetColonization->colonize(
                $colony,
                $userId,
                $building,
                $field
            );
        }


        $this->transferCrewToColony($ship, $colony);

        $this->createUserDepositMinings($colony);

        $this->shipRemover->remove($ship);

        $game->checkDatabaseItem($colony->getColonyClass()->getDatabaseId());

        $this->componentLoader->addComponentUpdate(ComponentEnum::COLONIES_NAVLET);

        $game->redirectTo(sprintf(
            '/colony.php?%s=1&id=%d',
            ShowColony::VIEW_IDENTIFIER,
            $colony->getId()
        ));
    }

    private function getCommodity(int $commodityId): CommodityInterface
    {
        $commodity = $this->commodityRepository->find($commodityId);
        if ($commodity === null) {
            throw new InvalidArgumentException(sprintf('commodityId %d does not exist', $commodityId));
        }

        return $commodity;
    }

    private function transferCrewToColony(ShipInterface $ship, ColonyInterface $colony): void
    {
        foreach ($ship->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $colony);
        }
    }

    private function createUserDepositMinings(ColonyInterface $colony): void
    {
        $deposits = $colony->getColonyClass()->getColonyClassDeposits();
        $userMinings = $colony->getUserDepositMinings();

        foreach ($deposits as $deposit) {
            //check if user already mined this commodity on this colony
            if (array_key_exists($deposit->getCommodity()->getId(), $userMinings)) {
                continue;
            }

            //create new mining entry
            $depositMining = $this->colonyDepositMiningRepository->prototype();
            $depositMining->setUser($colony->getUser());
            $depositMining->setColony($colony);
            $depositMining->setCommodity($deposit->getCommodity());
            $depositMining->setAmountLeft(random_int($deposit->getMinAmount(), $deposit->getMaxAmount()));

            $this->colonyDepositMiningRepository->save($depositMining);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
