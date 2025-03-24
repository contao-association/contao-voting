<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class ModuleMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_module', 'tl_content'])) {
            return false;
        }

        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_module WHERE type IN ('voting_list', 'voting_enquiry_list', 'voting_enquiry_reader')") > 0;
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!\array_key_exists('jumpto', $schemaManager->listTableColumns('tl_content'))) {
            $this->connection->executeStatement('ALTER TABLE tl_content ADD COLUMN jumpTo int(10) unsigned NOT NULL default 0');
        }

        $modules = $this->connection->fetchAllAssociative("SELECT id, type, jumpTo FROM tl_module WHERE type IN ('voting_list', 'voting_enquiry_list', 'voting_enquiry_reader')");

        foreach ($modules as $module) {
            $this->connection->update(
                'tl_content',
                ['type' => $module['type'], 'jumpTo' => $module['jumpTo']],
                ['type' => 'module', 'module' => $module['id']],
            );
        }

        $this->connection->executeStatement("DELETE FROM tl_module WHERE type IN ('voting_list', 'voting_enquiry_list', 'voting_enquiry_reader')");

        return $this->createResult(true);
    }
}
