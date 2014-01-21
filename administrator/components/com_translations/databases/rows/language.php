<?php

class ComTranslationsDatabaseRowLanguage extends KDatabaseRowDefault {
	public function getTranslatedPercentage() {
		$translation = $this->getService('com://admin/translations.model.translations')->original($this->lang_code)->getList()->top();

		if($translation->id) {
			$relations = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->getTotal();
			$translations = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->translated(1)->getTotal();

			return $translations . '/' . $relations;
		}

		return;
	}
}