<?php

class ComTranslationsDatabaseRowLanguage extends KDatabaseRowDefault
{
    /**
     * @return string
     */
    public function getTranslatedPercentage()
    {
		$translation = $this->getService('com://admin/translations.model.translations')->original($this->lang_code)->getList();

		if($translation instanceof KDatabaseRowsetDefault) {
			$translation->top();
        }

		if($translation->id) {
			$relations = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->getTotal();
			$translations = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->translated(1)->getTotal();

			return $translations . '/' . $relations;
		}

		return '0/0';
	}
}