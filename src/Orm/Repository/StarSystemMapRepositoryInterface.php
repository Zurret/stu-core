<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;

/**
 * @extends ObjectRepository<StarSystemMap>
 *
 * @method null|StarSystemMapInterface find(integer $id)
 */
interface StarSystemMapRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<StarSystemMapInterface>
     */
    public function getBySystemOrdered(int $starSystemId): array;

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMapInterface;

    /**
     * @return array<StarSystemMapInterface>
     */
    public function getByCoordinateRange(
        StarSystemInterface $starSystem,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy
    ): array;

    /**
     * @return array<int, array{id: int}>
     */
    public function getRandomFieldsForAstroMeasurement(int $starSystemId): array;

    /**
     * @return array<array{category_name: string, amount: int}>
     */
    public function getRumpCategoryInfo(int $cx, int $cy): array;

    public function save(StarSystemMapInterface $starSystemMap): void;

    /**
     * @return array<StarSystemMapInterface>
     */
    public function getForSubspaceEllipseCreation(): array;
}
