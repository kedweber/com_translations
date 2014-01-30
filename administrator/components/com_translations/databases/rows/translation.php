<?php

class ComTranslationsDatabaseRowTranslation extends KDatabaseRowDefault {
	public function delete() {
		$this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($this->id)->getList()->delete();

		return parent::delete();
	}
}