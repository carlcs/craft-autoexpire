<?php
namespace Craft;

class AutoExpire_RuleModel extends BaseModel
{
        protected function defineAttributes()
        {
                return array(
                        'id'             => AttributeType::Number,
                        'name'           => AttributeType::String,
                        'section'        => AttributeType::Number,
                        'entryType'      => AttributeType::Number,
                        'field'          => AttributeType::String,
                        'expirationDate' => AttributeType::String,
                        'allowOverwrite' => AttributeType::Bool,
                );
        }
}
