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
        return [
            'settings/plugins/autoexpire/index' => 'autoexpire/settings/index',
            'settings/plugins/autoexpire/new' => 'autoexpire/settings/_edit',
            'settings/plugins/autoexpire/(?P<ruleId>\d+)' => 'autoexpire/settings/_edit',
        ];
    }

    // Properties
    // =========================================================================

    /**
     * Used to keep track of entries and rules we already re-saved.
     *
     * @var array
     */
    private $_handledRules = [];

    /**
     * Used to keep track of entries which are handled by entries.onSaveEntry.
     *
     * @var array
     */
    private $_handledOnEntrySave = [];

    /**
     * A list of of EntryModel attributes of type DateTime.
     *
     * @var array
     */
    private static $_dateAttributes = ['postDate', 'expiryDate'];

    // Public Methods
    // =========================================================================

    /**
     * Initializes the plugin
     */
    public function init()
    {
        craft()->on('entries.onBeforeSaveEntry', [$this, 'handleBeforeEntrySave']);
        craft()->on('entries.onSaveEntry', [$this, 'handleEntrySave']);
        craft()->on('elements.onSaveElement', [$this, 'handleElementSave']);
    }

    /**
     * Make sure requirements are met before installation.
     *
     * @return bool
     * @throws Exception
     */
    public function onBeforeInstall()
    {
        if (!defined('PHP_VERSION') || version_compare(PHP_VERSION, '5.4', '<')) {
            throw new Exception($this->getName().' plugin requires PHP 5.4 or later.');
        }
    }

    /**
     * Keeps track of entries which are handled by entries.onSaveEntry.
     *
     * @param Event $event
     */
    public function handleBeforeEntrySave(Event $event){
        $entry = $event->params['entry'];

        $this->_handledOnEntrySave[] = $entry->id;
    }

    /**
     * Applies the rules in case EntriesService::saveEntry() was used.
     *
     * @param Event $event
     */
    public function handleEntrySave(Event $event){
        $entry = $event->params['entry'];

        $this->applyRules($entry);
    }

    /**
     * Applies the rules in case ElementsService::saveElement() was used directly.
     *
     * @param Event $event
     */
    public function handleElementSave(Event $event){
        $element = $event->params['element'];
        $isNewElement = $event->params['isNewElement'];

        if ($element->getElementType() !== ElementType::Entry) {
            return null;
        }

        // Will this re-save be taken care of by the entries.onSaveEntry event handler?
        if ($isNewElement || in_array($element->id, $this->_handledOnEntrySave)) {
            return null;
        }

        $this->applyRules($element);
    }

    /**
     * Applies the rules which relate to a given entry.
     *
     * @param EntryModel $entry
     */
    public function applyRules($entry)
    {
        foreach (craft()->autoExpire->getRules() as $rule) {
            if (($entry->sectionId == $rule->sectionId) && ($entry->getType()->id == $rule->entryTypeId)) {
                $ruleName = $entry->id.'::'.$rule->id;

                // Did we already re-save the entry for this rule? Necessary because of the recursive
                // call of EntriesService::saveEntry().
                if (!in_array($ruleName, $this->_handledRules)) {
                    $this->_handledRules[] = $ruleName;

                    $this->applyRule($entry, $rule);
                }
            }
        }
    }

    /**
     * Re-saves an entry with the date from a rule.
     *
     * @param EntryModel $entry
     * @param AutoExpire_RuleModel $rule
     */
    public function applyRule($entry, $rule)
    {
        $fieldHandle = $rule['fieldHandle'];
        $fieldIsEmpty = $this->fieldIsEmpty($entry, $fieldHandle);

        if ($fieldIsEmpty || !$rule->allowOverwrite) {
            try {
                $newDateString = craft()->templates->renderObjectTemplate($rule->dateTemplate, $entry);
            } catch (\Exception $e) {
                AutoExpirePlugin::log('Couldn’t render template for entry with id “'.$entry->id .
                    '” and Auto Expire rule “'.$rule->name.'” ('.$e->getMessage().').', LogLevel::Error);
                return null;
            }

            $newDate = DateTime::createFromString($newDateString);

            if (!$newDate instanceof \DateTime) {
                AutoExpirePlugin::log('Couldn’t create date from string “'.$newDateString.'” for entry with id “' .
                    $entry->id.'” and Auto Expire rule “'.$rule->name.'”.', LogLevel::Error);
                return null;
            }

            if (in_array($fieldHandle, static::$_dateAttributes)) {
                $entry->{$fieldHandle} = $newDate;
            } else {
                $entry->setContentFromPost(array($fieldHandle => $newDate));
            }

            $success = craft()->entries->saveEntry($entry);

            if (!$success) {
                AutoExpirePlugin::log('Couldn’t save entry with id “'.$entry->id.'” and Auto Expire rule “' .
                    $rule->name.'”.', LogLevel::Error);
            }
        }
    }

    /**
     * Returns whether an entry's date field is empty.
     *
     * @param EntryModel $entry
     * @param string $fieldHandle
     *
     * @return bool
     */
    protected function fieldIsEmpty($entry, $fieldHandle)
    {
        if ($fieldHandle == 'postDate') {
            return $entry->{$fieldHandle} == DateTimeHelper::currentUTCDateTime();
        }

        return $entry->{$fieldHandle} === null;
    }
}
