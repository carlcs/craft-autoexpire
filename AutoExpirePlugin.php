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
     * Used to keep track of entries and rules we already re-saved.
     *
     * @var array
     */
    public $_handledRules = array();

    /**
     * Used to keep track of entries which are handled by entries.onSaveEntry.
     *
     * @var array
     */
    public $_handledOnEntrySave = array();

    // Public Methods
    // =========================================================================

    /**
     * Initializes the plugin
     */
    public function init()
    {
        craft()->on('entries.onBeforeSaveEntry', array($this, 'handleBeforeEntrySave'));
        craft()->on('entries.onSaveEntry', array($this, 'handleEntrySave'));

        craft()->on('elements.onSaveElement', array($this, 'handleElementSave'));
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
            if (($entry->sectionId == $rule->section) && ($entry->getType()->id == $rule->entryType)) {
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
        $fieldHandle = $rule['field'];
        $fieldIsEmpty = $this->fieldIsEmpty($entry, $fieldHandle);

        if ($fieldIsEmpty || !$rule->allowOverwrite) {
            $newExpiryDate = craft()->templates->renderObjectTemplate($rule->expirationDate, $entry);
            $newExpiryDate = DateTime::createFromString($newExpiryDate);

            if (!$newExpiryDate instanceof \DateTime) {
                BusinessLogicPlugin::log('(Auto Expire) Couldn’t create a date for “'.$rule->name.'”', LogLevel::Error);
            }

            if (in_array($fieldHandle, array('postDate', 'expiryDate'))) {
                $entry->{$fieldHandle} = $newExpiryDate;
            } else {
                $entry->setContentFromPost(array($fieldHandle => $newExpiryDate));
            }

            $success = craft()->entries->saveEntry($entry);

            if (!$success) {
                BusinessLogicPlugin::log('(Auto Expire) Couldn’t save the entry “'.$entry->title.'”', LogLevel::Error);
            } else {
                BusinessLogicPlugin::log('(Auto Expire) Rule “'.$rule['name'].'” applied to the entry “'.$entry->title.'”.');
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
