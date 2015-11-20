<?php
namespace Craft;

class AutoExpireService extends BaseApplicationComponent
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns all rules.
	 *
	 * @return array
	 */
        public function getRules()
	{
		$ruleRecords = $this->_getRuleRecords();

		if (count($ruleRecords) > 0)
		{
			return AutoExpire_RuleModel::populateModels($ruleRecords);
		}

		return array();
	}

	/**
	 * Returns a rule by its ID.
	 *
	 * @param int $ruleId
	 * @return RuleModel
	 */
	public function getRuleById($ruleId)
	{
		$ruleRecord = AutoExpire_RuleRecord::model()->findById($ruleId);

		if ($ruleRecord)
		{
			return AutoExpire_RuleModel::populateModel($ruleRecord);
		}
	}

	/**
	 * Saves a rule.
	 *
	 * @param RuleModel $rule
	 * @return bool
	 */
	public function saveRule(AutoExpire_RuleModel $rule)
	{
		$ruleRecord = $this->_getRuleRecordById($rule->id);

		$ruleRecord->name           = $rule->name;
		$ruleRecord->section        = $rule->section;
		$ruleRecord->entryType      = $rule->entryType;
                $ruleRecord->field          = $rule->field;
		$ruleRecord->expirationDate = $rule->expirationDate;
		$ruleRecord->allowOverwrite = $rule->allowOverwrite;

		$recordValidates = $ruleRecord->validate();

		if ($recordValidates)
		{
			if ($ruleRecord->isNewRecord())
			{
				$maxSortOrder = craft()->db->createCommand()
					->select('max(sortOrder)')
					->from('autoexpire')
					->queryScalar();

				$ruleRecord->sortOrder = $maxSortOrder + 1;
			}

			$ruleRecord->save(false);

			// Now that we have a rule ID, save it on the model
			if (!$rule->id)
			{
				$rule->id = $ruleRecord->id;
			}

			return true;
		}
		else
		{
			$rule->addErrors($ruleRecord->getErrors());

			return false;
		}
	}

	/**
	 * Deletes a rule.
	 *
	 * @param int $ruleId
	 * @return bool
	 */
	public function deleteRuleById($ruleId)
	{
		craft()->db->createCommand()->delete('autoexpire', array('id' => $ruleId));
		return true;
	}

	/**
	 * Reorders rules.
	 *
	 * @param array $ruleIds
	 * @return null
	 */
	public function reorderRules($ruleIds)
	{
		foreach ($ruleIds as $order => $ruleId)
		{
			$data = array('sortOrder' => $order + 1);
			$condition = array('id' => $ruleId);
			craft()->db->createCommand()->update('autoexpire', $data, $condition);
		}
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns a rule's record.
	 *
	 * @param int $ruleId
	 * @return RuleRecord
	 */
	private function _getRuleRecordById($ruleId = null)
	{
		if ($ruleId)
		{
			$ruleRecord = AutoExpire_RuleRecord::model()->findById($ruleId);

			if (!$ruleRecord)
			{
				throw new Exception(Craft::t('No rule exists with the ID “{id}”.', array('id' => $ruleId)));
			}
		}
		else
		{
			$ruleRecord = new AutoExpire_RuleRecord();
		}

		return $ruleRecord;
	}

	/**
	 * Returns all rule rocords.
	 *
	 * @return array
	 */
        private function _getRuleRecords()
	{
		return AutoExpire_RuleRecord::model()->ordered()->findAll();
	}
}
