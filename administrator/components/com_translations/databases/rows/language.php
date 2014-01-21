<?php

class ComTranslationsDatabaseRowLanguage extends KDatabaseRowDefault {
	public function getTranslatedPercentage() {
		$model = $this->getService('com://admin/translations.model.translations');

		$total = $model->lang($this->lang_code)->getTotal();
		$translated = $model->translated(true)->lang($this->lang_code)->getTotal();

		return $translated . '/' . $total;
	}
}