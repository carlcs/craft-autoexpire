<?php
namespace Craft;

class AutoExpirePlugin extends BasePlugin
{
        function getName()
        {
                return 'Auto Expire';
        }

        function getVersion()
        {
                return '1.1';
        }

        function getSchemaVersion()
	{
		return '1.1';
	}

        function getDeveloper()
        {
                return 'carlcs';
        }

        function getDeveloperUrl()
        {
                return 'https://github.com/carlcs/craft-autoexpire';
        }

        function getDocumentationUrl()
        {
                return 'https://github.com/carlcs/craft-autoexpire';
        }

        function getReleaseFeedUrl()
        {
                return 'https://github.com/carlcs/craft-autoexpire/raw/master/releases.json';
        }

        public function getSettingsUrl()
        {
                return 'settings/plugins/autoexpire/index';
        }

        public function registerCpRoutes()
        {
                return array(
                        'settings/plugins/autoexpire/index' => 'autoexpire/settings/index',
                        'settings/plugins/autoexpire/new' => 'autoexpire/settings/_edit',
                        'settings/plugins/autoexpire/(?P<ruleId>\d+)' => 'autoexpire/settings/_edit',
                );
        }

        public function init()
        {
                parent::init();

                craft()->on('entries.onSaveEntry', function(Event $event)
                {
                        $entry = $event->params['entry'];
                        $rules = craft()->autoExpire->getRules();

                        // Used to break the recursive call of `saveEntry`.
                        if (!isset($executedRules)) static $executedRules = array();

                        foreach($rules as $rule)
                        {
                                if (($entry->section->id == $rule->section) && ($entry->type->id == $rule->entryType))
                                {
                                        if (!in_array($rule['id'], $executedRules) && (($entry->expiryDate === null) || (!$rule->allowOverwrite)))
                                        {
                                                $executedRules[] = $rule['id'];

                                                $newExpiryDate = craft()->templates->renderObjectTemplate($rule->expirationDate, $entry);
                                                $newExpiryDate = DateTime::createFromString($newExpiryDate);

                                                if (!$newExpiryDate instanceof \DateTime)
                                                {
                                                        Craft::log('(Auto Expire) Couldn’t create a date for “'.$rule->name.'”', LogLevel::Error);

                                                        break;
                                                }

                                                $entry[$rule['field']] = $newExpiryDate;

                                                $success = craft()->entries->saveEntry($entry);

                                                if (!$success)
                                                {
                                                        Craft::log('(Auto Expire) Couldn’t save the entry “'.$entry->title.'”', LogLevel::Error);
                                                }
                                                else
                                                {
                                                        Craft::log('(Auto Expire) Rule “'.$rule['name'].'” applied to the entry “'.$entry->title.'”.');
                                                }
                                        }
                                }
                        }
                });
        }
}
