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
