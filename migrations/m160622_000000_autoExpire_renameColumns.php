<?php
namespace Craft;

class m160622_000000_autoExpire_renameColumns extends BaseMigration
{
    public function safeUp()
    {
        MigrationHelper::renameColumn('autoexpire', 'section', 'sectionId');
        MigrationHelper::renameColumn('autoexpire', 'entryType', 'entryTypeId');
        MigrationHelper::renameColumn('autoexpire', 'field', 'fieldHandle');
        MigrationHelper::renameColumn('autoexpire', 'expirationDate', 'dateTemplate');
        // MigrationHelper::renameColumn('autoexpire', 'allowOverwrite', 'allowOverwrite');

        return true;
    }
}
