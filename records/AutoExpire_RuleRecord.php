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
        return array(
            'name'           => array(AttributeType::String, 'label' => Craft::t('Name'), 'required' => true),
            'section'        => array(AttributeType::Number, 'required' => true),
            'entryType'      => array(AttributeType::Number, 'required' => true),
            'field'          => array(AttributeType::String, 'required' => true),
            'expirationDate' => array(AttributeType::String, 'label' => Craft::t('Date/Time'), 'required' => true),
            'allowOverwrite' => array(AttributeType::Bool, 'default' => true),
            'sortOrder'      => AttributeType::SortOrder,
        );
    }
}
