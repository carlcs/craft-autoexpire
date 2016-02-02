<?php
namespace Craft;

class AutoExpireController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Saves a new or existing rule.
     *
     * @return null
     */
    public function actionSaveRule()
    {
        $this->requirePostRequest();

        $rule = new AutoExpire_RuleModel();
        $rule->id             = craft()->request->getPost('id');
        $rule->name           = craft()->request->getPost('name');
        $rule->section        = craft()->request->getPost('section');
        $rule->field          = craft()->request->getPost('field');
        $rule->expirationDate = craft()->request->getPost('expirationDate');
        $rule->allowOverwrite = craft()->request->getPost('allowOverwrite');

        // Extract the entry type from sections array
        $sections = craft()->request->getPost('sections');
        if (isset($sections[$rule->section])) {
            $rule->entryType = $sections[$rule->section]['entryType'];
        }

        // Did it save?
        if (craft()->autoExpire->saveRule($rule)) {
            craft()->userSession->setNotice(Craft::t('Rule saved.'));
            $this->redirectToPostedUrl();
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save rule.'));
        }

        // Send the widget back to the template
        craft()->urlManager->setRouteVariables(array(
            'rule' => $rule
        ));
    }

    /**
     * Deletes a rule.
     *
     * @return null
     */
    public function actionDeleteRule()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $ruleId = JsonHelper::decode(craft()->request->getRequiredPost('id'));
        craft()->autoExpire->deleteRuleById($ruleId);

        $this->returnJson(array('success' => true));
    }

    /**
     * Updates the rules sort order.
     *
     * @return null
     */
    public function actionReorderRules()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $ruleIds = JsonHelper::decode(craft()->request->getRequiredPost('ids'));

        craft()->autoExpire->reorderRules($ruleIds);

        $this->returnJson(array('success' => true));
    }
}
