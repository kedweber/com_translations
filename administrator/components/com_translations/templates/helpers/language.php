<?php

class ComTranslationsTemplateHelperLanguage extends KTemplateHelperAbstract {
	public function translations($config = array()) {
		$config = new KConfig($config);
		$config->append(array(
			'row' => null,
			'table' => '',
		));

		$translation = $this->getService('com://admin/translations.model.translations')->row($config->row)->table($config->table)->getList()->top();
		$relations = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->getList();

		// The original is always translated.
		$html = '';

		if($translation) {
			$html .= '<style src="media://com_translations/css/translations.css" />';
			$html .= '<a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $translation->original) . '"><div class="badge badge-success">' . substr($translation->original, 3, 5) . '</a></div>';

			foreach($relations as $language) {
				if($language->translated) {
					$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang) . '"><div class="badge badge-success">' . substr($language->lang, 3, 5) . '</a></div>';
				} else {
					$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang) . '"><div class="badge badge-important">' . substr($language->lang, 3, 5) . '</a></div>';
				}
			}
		}

		return $html;
	}
}