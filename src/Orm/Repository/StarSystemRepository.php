<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemInterface;

/**
 * @extends EntityRepository<StarSystem>
 */
final class StarSystemRepository extends EntityRepository implements StarSystemRepositoryInterface
{
    public function prototype(): StarSystemInterface
    {
        return new StarSystem();
    }

    public function save(StarSystemInterface $storage): void
    {
        $em = $this->getEntityManager();

        $em->persist($storage);
    }

    public function getByLayer(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                    JOIN %s m
                    WITH m.systems_id = s.id
                    WHERE m.layer_id  = :layerId
                    ORDER BY s.name ASC',
                    StarSystem::class,
                    Map::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t WHERE t.database_id NOT IN (SELECT d.id FROM %s d WHERE d.category_id = :categoryId)',
                    StarSystem::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STARSYSTEM,
            ])
            ->getResult();
    }

    public function getNumberOfSystemsToGenerate(LayerInterface $layer): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(m.id) from %s m
                    WHERE m.system_type_id IS NOT NULL
                    AND m.systems_id IS NULL
                    AND m.layer = :layer',
                    Map::class
                )
            )
            ->setParameters([
                'layer' => $layer
            ])
            ->getSingleScalarResult();
    }

    public function getPreviousStarSystem(StarSystemInterface $current): ?StarSystemInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    JOIN %s m
                    WITH m.systems_id = ss.id
                    WHERE ss.id < :currentId
                    AND m.layer = :layer
                    ORDER BY ss.id DESC',
                    StarSystem::class,
                    Map::class
                )
            )
            ->setParameters(['layer' => $current->getLayer(), 'currentId' => $current->getId()])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getNextStarSystem(StarSystemInterface $current): ?StarSystemInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    JOIN %s m
                    WITH m.systems_id = ss.id
                    WHERE ss.id > :currentId
                    AND m.layer = :layer
                    ORDER BY ss.id ASC',
                    StarSystem::class,
                    Map::class
                )
            )
            ->setParameters(['layer' => $current->getLayer(), 'currentId' => $current->getId()])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getPirateHides(ShipInterface $ship): array
    {
        $layer = $ship->getLayer();
        if ($layer === null) {
            return [];
        }

        $location = $ship->getLocation();
        $range = $ship->getSensorRange() * 4;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ss FROM %s ss
                JOIN %s m
                WITH ss.id = m.systems_id
                WHERE ss.cx BETWEEN :minX AND :maxX
                AND ss.cy BETWEEN :minY AND :maxY
                AND m.layer_id = :layerId',
                StarSystem::class,
                Map::class
            )
        )
            ->setParameters([
                'minX' => $location->getCx() - $range,
                'maxX' => $location->getCx() + $range,
                'minY' => $location->getCy() - $range,
                'maxY' => $location->getCy() + $range,
                'layerId' => $layer->getId()
            ])
            ->getResult();
    }
}
