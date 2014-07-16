<?php

class ComTranslationsDatabaseRowTranslation extends KDatabaseRowDefault
{
	/**
	 * @return mixed
	 */
	public function getOriginal()
	{
		return $this->getService('com://admin/translations.model.translations')->row($this->row)->table($this->table)->original(1)->getItem();
	}

	/**
	 * @return bool|void
	 */
	public function save()
	{
		if(!$this->load() && !$this->getOriginal()->original) {
			$this->original = 1;
			$this->translated = 1;
		}

		$this->lang = substr(JFactory::getLanguage()->getTag(), 0, 2);

		parent::save();
	}
}