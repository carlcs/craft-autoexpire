<?php
namespace Craft;

class AutoExpire_RuleModel extends BaseModel
{
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
            'id'             => AttributeType::Number,
            'name'           => AttributeType::String,
            'sectionId'      => AttributeType::Number,
            'entryTypeId'    => AttributeType::Number,
            'fieldHandle'    => AttributeType::String,
            'dateTemplate'   => AttributeType::String,
            'allowOverwrite' => AttributeType::Bool,
        );
    }
}
