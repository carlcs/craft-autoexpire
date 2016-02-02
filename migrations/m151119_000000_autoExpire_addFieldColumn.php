<?php
namespace Craft;

class m151119_000000_autoExpire_addFieldColumn extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->addColumnAfter('autoexpire', 'field', ColumnType::Varchar, 'entryType');

        $rules = craft()->db->createCommand()->select('*')->from('autoexpire')->queryAll();

        foreach ($rules as $rule) {
            $columns = array('field' => 'expiryDate');

            craft()->db->createCommand()->update('autoexpire', $columns, 'id = :id', array(':id' => $rule['id']));
        }

        return true;
    }
}
