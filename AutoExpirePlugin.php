<?php
namespace Craft;

class AutoExpirePlugin extends BasePlugin
{
    public function getName()
    {
        return 'Auto Expire';
    }

    public function getVersion()
    {
        return '1.1.1';
    }

    public function getSchemaVersion()
    {
        return '1.1';
    }

    public function getDeveloper()
    {
        return 'carlcs';
    }

    public function getDeveloperUrl()
    {
        return 'https://github.com/carlcs';
    }

    public function getDocumentationUrl()
    {
        return 'https://github.com/carlcs/craft-autoexpire';
    }

    public function getReleaseFeedUrl()
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

    // Properties
    // =========================================================================

    /**
     * Used to keep track of elements and rules we already re-saved with the
     * new date attributes set.
     *
     * @var array
     */
    public $_handledRules = array();

    // Public Methods
    // =========================================================================

    /**
     * Initializes the plugin
     */
    public function init()
    {
        craft()->on('entries.onSaveEntry', array($this, 'handleEntrySave'));
    }

    /**
     * Re-saves an entry on save with the new date attributes set from the rules.
     *
     * @param Event $event
     */
    public function handleEntrySave(Event $event)
    {
        $entry = $event->params['entry'];
        $rules = craft()->autoExpire->getRules();

        foreach ($rules as $rule) {
            if (($entry->section->id == $rule->section) && ($entry->type->id == $rule->entryType)) {

                if (!in_array($rule['id'], $this->_handledRules)) {
                    $this->_handledRules[] = $rule['id'];

                    if (($entry->expiryDate === null) || (!$rule->allowOverwrite)) {
                        $newExpiryDate = craft()->templates->renderObjectTemplate($rule->expirationDate, $entry);
                        $newExpiryDate = DateTime::createFromString($newExpiryDate);

                        if (!$newExpiryDate instanceof \DateTime) {
                            Craft::log('(Auto Expire) Couldn’t create a date for “'.$rule->name.'”', LogLevel::Error);
                            break;
                        }

                        $entry[$rule['field']] = $newExpiryDate;

                        $success = craft()->entries->saveEntry($entry);

                        if (!$success) {
                            Craft::log('(Auto Expire) Couldn’t save the entry “'.$entry->title.'”', LogLevel::Error);
                        } else {
                            Craft::log('(Auto Expire) Rule “'.$rule['name'].'” applied to the entry “'.$entry->title.'”.');
                        }
                    }
                }
            }
        }
    }
}
