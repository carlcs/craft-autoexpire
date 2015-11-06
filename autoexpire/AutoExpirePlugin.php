<?php
namespace Craft;

/**
 * Auto Expire plugin
 */
class AutoExpirePlugin extends BasePlugin
{
        function getName()
        {
                return 'Auto Expire';
        }

        function getVersion()
        {
                return '1.0';
        }

        function getSchemaVersion()
	{
		return null;
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

                        $rules = array_reverse($rules);

                        foreach($rules as $rule)
                        {
                                // Used to break the recursive call of `saveEntry`.
                                static $recursionLevel = 0;

                                if (($entry->section->id == $rule->section) && ($entry->type->id == $rule->entryType))
                                {
                                        if (($recursionLevel == 0) && (($entry->expiryDate === null) || (!$rule->allowOverwrite)))
                                        {
                                                $recursionLevel++;

                                                $newExpiryDate = craft()->templates->renderObjectTemplate($rule->expirationDate, $entry);
                                                $newExpiryDate = DateTime::createFromString($newExpiryDate);

                                                if (!$newExpiryDate instanceof \DateTime)
                                                {
                                                        Craft::log('(Auto Expire) Couldn’t create a date for “'.$rule->name.'”', LogLevel::Error);

                                                        break;
                                                }

                                                $entry->expiryDate = $newExpiryDate;

                                                $success = craft()->entries->saveEntry($entry);

                                                if (!$success)
                                                {
                                                        Craft::log('(Auto Expire) Couldn’t save the entry “'.$entry->title.'”', LogLevel::Error);
                                                }
                                        }
                                }
                        }
                });
        }
}
