<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class AstroEntryLib implements AstroEntryLibInterface
{
    private AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function cancelAstroFinalizing(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn(null)->update();

        $entry = $this->astroEntryRepository->getByShipLocation($ship, false);

        $entry->setState(AstronomicalMappingEnum::MEASURED);
        $entry->setAstroStartTurn(null);
        $this->astroEntryRepository->save($entry);
    }

    public function finish(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn(null)->update();

        $entry = $this->astroEntryRepository->getByShipLocation($ship, false);

        $entry->setState(AstronomicalMappingEnum::DONE);
        $entry->setAstroStartTurn(null);
        $this->astroEntryRepository->save($entry);
    }
}
