<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColonyFieldData extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds test colony field data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id)
            VALUES 
            (2, 76777, 0, 900, NULL, NULL, 0, 0, true, NULL),
            (3, 76777, 1, 900, NULL, NULL, 0, 0, true, NULL),
            (4, 76777, 2, 900, NULL, NULL, 0, 0, true, NULL),
            (5, 76777, 3, 900, NULL, NULL, 0, 0, true, NULL),
            (6, 76777, 4, 900, NULL, NULL, 0, 0, true, NULL),
            (7, 76777, 5, 900, NULL, NULL, 0, 0, true, NULL),
            (8, 76777, 6, 900, NULL, NULL, 0, 0, true, NULL),
            (9, 76777, 7, 900, NULL, NULL, 0, 0, true, NULL),
            (10, 76777, 8, 900, NULL, NULL, 0, 0, true, NULL),
            (11, 76777, 9, 900, NULL, NULL, 0, 0, true, NULL),
            (12, 76777, 10, 900, NULL, NULL, 0, 0, true, NULL),
            (13, 76777, 11, 900, NULL, NULL, 0, 0, true, NULL),
            (14, 76777, 12, 900, NULL, NULL, 0, 0, true, NULL),
            (15, 76777, 13, 900, NULL, NULL, 0, 0, true, NULL),
            (16, 76777, 14, 900, NULL, NULL, 0, 0, true, NULL),
            (17, 76777, 15, 900, NULL, NULL, 0, 0, true, NULL),
            (18, 76777, 16, 900, NULL, NULL, 0, 0, true, NULL),
            (19, 76777, 17, 900, NULL, NULL, 0, 0, true, NULL),
            (20, 76777, 18, 900, NULL, NULL, 0, 0, true, NULL),
            (21, 76777, 19, 900, NULL, NULL, 0, 0, true, NULL),
            (22, 76777, 20, 701, NULL, NULL, 0, 0, true, NULL),
            (23, 76777, 21, 701, NULL, NULL, 0, 0, true, NULL),
            (24, 76777, 22, 501, NULL, NULL, 0, 0, true, NULL),
            (25, 76777, 23, 201, NULL, NULL, 0, 0, true, NULL),
            (27, 76777, 25, 112, NULL, NULL, 0, 0, true, NULL),
            (28, 76777, 26, 501, NULL, NULL, 0, 0, true, NULL),
            (29, 76777, 27, 112, NULL, NULL, 0, 0, true, NULL),
            (30, 76777, 28, 112, NULL, NULL, 0, 0, true, NULL),
            (32, 76777, 30, 111, NULL, NULL, 0, 0, true, NULL),
            (33, 76777, 31, 701, NULL, NULL, 0, 0, true, NULL),
            (34, 76777, 32, 101, NULL, NULL, 0, 0, true, NULL),
            (35, 76777, 33, 101, NULL, NULL, 0, 0, true, NULL),
            (36, 76777, 34, 101, NULL, NULL, 0, 0, true, NULL),
            (37, 76777, 35, 201, NULL, NULL, 0, 0, true, NULL),
            (38, 76777, 36, 201, NULL, NULL, 0, 0, true, NULL),
            (39, 76777, 37, 111, NULL, NULL, 0, 0, true, NULL),
            (40, 76777, 38, 701, NULL, NULL, 0, 0, true, NULL),
            (41, 76777, 39, 101, NULL, NULL, 0, 0, true, NULL),
            (42, 76777, 40, 101, NULL, NULL, 0, 0, true, NULL),
            (43, 76777, 41, 101, NULL, NULL, 0, 0, true, NULL),
            (44, 76777, 42, 101, NULL, NULL, 0, 0, true, NULL),
            (45, 76777, 43, 401, NULL, NULL, 0, 0, true, NULL),
            (46, 76777, 44, 101, NULL, NULL, 0, 0, true, NULL),
            (47, 76777, 45, 201, NULL, NULL, 0, 0, true, NULL),
            (48, 76777, 46, 111, NULL, NULL, 0, 0, true, NULL),
            (49, 76777, 47, 111, NULL, NULL, 0, 0, true, NULL),
            (50, 76777, 48, 111, NULL, NULL, 0, 0, true, NULL),
            (51, 76777, 49, 111, NULL, NULL, 0, 0, true, NULL),
            (52, 76777, 50, 701, NULL, NULL, 0, 0, true, NULL),
            (53, 76777, 51, 401, NULL, NULL, 0, 0, true, NULL),
            (54, 76777, 52, 401, NULL, NULL, 0, 0, true, NULL),
            (55, 76777, 53, 101, NULL, NULL, 0, 0, true, NULL),
            (56, 76777, 54, 101, NULL, NULL, 0, 0, true, NULL),
            (57, 76777, 55, 111, NULL, NULL, 0, 0, true, NULL),
            (58, 76777, 56, 111, NULL, NULL, 0, 0, true, NULL),
            (59, 76777, 57, 201, NULL, NULL, 0, 0, true, NULL),
            (60, 76777, 58, 201, NULL, NULL, 0, 0, true, NULL),
            (61, 76777, 59, 201, NULL, NULL, 0, 0, true, NULL),
            (62, 76777, 60, 101, NULL, NULL, 0, 0, true, NULL),
            (63, 76777, 61, 101, NULL, NULL, 0, 0, true, NULL),
            (64, 76777, 62, 701, NULL, NULL, 0, 0, true, NULL),
            (65, 76777, 63, 701, NULL, NULL, 0, 0, true, NULL),
            (66, 76777, 64, 201, NULL, NULL, 0, 0, true, NULL),
            (67, 76777, 65, 201, NULL, NULL, 0, 0, true, NULL),
            (68, 76777, 66, 201, NULL, NULL, 0, 0, true, NULL),
            (69, 76777, 67, 201, NULL, NULL, 0, 0, true, NULL),
            (70, 76777, 68, 201, NULL, NULL, 0, 0, true, NULL),
            (71, 76777, 69, 201, NULL, NULL, 0, 0, true, NULL),
            (72, 76777, 70, 501, NULL, NULL, 0, 0, true, NULL),
            (73, 76777, 71, 101, NULL, NULL, 0, 0, true, NULL),
            (74, 76777, 72, 112, NULL, NULL, 0, 0, true, NULL),
            (75, 76777, 73, 501, NULL, NULL, 0, 0, true, NULL),
            (76, 76777, 74, 501, NULL, NULL, 0, 0, true, NULL),
            (77, 76777, 75, 201, NULL, NULL, 0, 0, true, NULL),
            (78, 76777, 76, 201, NULL, NULL, 0, 0, true, NULL),
            (79, 76777, 77, 201, NULL, NULL, 0, 0, true, NULL),
            (80, 76777, 78, 201, NULL, NULL, 0, 0, true, NULL),
            (81, 76777, 79, 201, NULL, NULL, 0, 0, true, NULL),
            (82, 76777, 80, 802, NULL, NULL, 0, 0, true, NULL),
            (83, 76777, 81, 801, NULL, NULL, 0, 0, true, NULL),
            (84, 76777, 82, 801, NULL, NULL, 0, 0, true, NULL),
            (85, 76777, 83, 801, NULL, NULL, 0, 0, true, NULL),
            (86, 76777, 84, 851, NULL, NULL, 0, 0, true, NULL),
            (87, 76777, 85, 851, NULL, NULL, 0, 0, true, NULL),
            (88, 76777, 86, 851, NULL, NULL, 0, 0, true, NULL),
            (89, 76777, 87, 802, NULL, NULL, 0, 0, true, NULL),
            (90, 76777, 88, 802, NULL, NULL, 0, 0, true, NULL),
            (91, 76777, 89, 801, NULL, NULL, 0, 0, true, NULL),
            (92, 76777, 90, 802, NULL, NULL, 0, 0, true, NULL),
            (93, 76777, 91, 801, NULL, NULL, 0, 0, true, NULL),
            (94, 76777, 92, 801, NULL, NULL, 0, 0, true, NULL),
            (95, 76777, 93, 802, NULL, NULL, 0, 0, true, NULL),
            (96, 76777, 94, 801, NULL, NULL, 0, 0, true, NULL),
            (97, 76777, 95, 851, NULL, NULL, 0, 0, true, NULL),
            (98, 76777, 96, 851, NULL, NULL, 0, 0, true, NULL),
            (99, 76777, 97, 802, NULL, NULL, 0, 0, true, NULL),
            (100, 76777, 98, 801, NULL, NULL, 0, 0, true, NULL),
            (101, 76777, 99, 801, NULL, NULL, 0, 0, true, NULL),
            (31, 76777, 29, 112, NULL, NULL, 0, 0, true, NULL),
            (26, 76777, 24, 101, 82010100, NULL, 1500, 1, true, NULL);'
        );
    }
}
