<?php
namespace Craft;

class AutoExpire_RuleRecord extends BaseRecord
{
        public function getTableName()
        {
                return 'autoexpire';
        }

        protected function defineAttributes()
        {
                return array(
                        'name'           => array(AttributeType::String, 'required' => true),
                        'section'        => array(AttributeType::Number, 'required' => true),
			'entryType'      => array(AttributeType::Number, 'required' => true),
                        'expirationDate' => array(AttributeType::String, 'required' => true),
                        'allowOverwrite' => array(AttributeType::Bool, 'default' => true),
                        'sortOrder'      => AttributeType::SortOrder,
                );
        }
}
