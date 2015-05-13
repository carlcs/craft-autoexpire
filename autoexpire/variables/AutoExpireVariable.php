<?php
namespace Craft;

class AutoExpireVariable
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns all stored rules.
	 *
	 * @return array
	 */
	public function getRules()
	{
		return craft()->autoExpire->getRules();
	}

	/**
	 * Returns a rule by its ID.
	 *
	 * @param int $id
	 * @return RuleModel|null
	 */
	public function getRuleById($id)
	{
		return craft()->autoExpire->getRuleById($id);
	}
}
