<?php

class ComTranslationsTemplateHelperLanguage extends KTemplateHelperAbstract {
	public function translations($config = array()) {
		$config = new KConfig($config);
		$config->append(array(
			'row' => null,
			'table' => '',
		));

		$rows = $this->getService('com://admin/translations.model.translations')->row($config->row)->table($config->table)->getList();

		// The original is always translated.
		$original = $rows->top();
		$html = '';

		if($original) {
			$html .= '<style src="media://com_translations/css/translations.css" />';
			$html .= '<a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $original->original) . '"><div class="badge badge-success">' . substr($original->original, 3, 5) . '</a></div>';

			foreach($rows as $language) {
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