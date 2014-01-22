<?php

class ComTranslationsTemplateHelperLanguage extends KTemplateHelperAbstract {
	public function translations($config = array()) {
		$config = new KConfig($config);
		$config->append(array(
			'row' => null,
			'table' => '',
		));

		$languages = $this->getService('com://admin/translations.model.languages')->connect(true)->row($config->row)->table($config->table)->getList();
		$original = $languages->top();

		// The original is always translated.
		$html = '';

		if($languages && $original) {
			$html .= '<style src="media://com_translations/css/translations.css" />';
			$html .= '<a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $original->original) . '"><div class="badge badge-success">' . substr($original->original, 3, 5) . '</a></div>';

			foreach($languages as $language) {
				if($language->lang_code != $original->original)
				{
					if($language->translated)
					{
						$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-success">' . substr($language->lang_code, 3, 5) . '</a></div>';
					}
					else
					{
						$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-important">' . substr($language->lang_code, 3, 5) . '</a></div>';
					}
				}
			}
		}

		return $html;
	}
}