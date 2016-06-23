<?php
namespace Craft;

class AutoExpire_RuleRecord extends BaseRecord
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the associated database table.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'autoexpire';
    }

    // Protected Methods
    // =========================================================================

    /**
     * Defines this model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return [
            'name'           => [AttributeType::String, 'label' => Craft::t('Name'), 'required' => true],
            'sectionId'      => [AttributeType::Number, 'required' => true],
            'entryTypeId'    => [AttributeType::Number, 'required' => true],
            'fieldHandle'    => [AttributeType::String, 'required' => true],
            'dateTemplate'   => [AttributeType::String, 'label' => Craft::t('Date/Time'), 'required' => true],
            'allowOverwrite' => [AttributeType::Bool, 'default' => true],
            'sortOrder'      => AttributeType::SortOrder,
        ];
    }
}
