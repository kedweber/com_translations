<?php

class ComTranslationsTemplateHelperLanguage extends KTemplateHelperAbstract {
	public function translations($config = array()) {
		$config = new KConfig($config);
		$config->append(array(
			'row' => null,
			'table' => '',
		));

		// First for our knowledge we get the original language (if exists.)
		$original = $this->_getOriginalLanguage($config);
		$html = '<style src="media://com_translations/css/translations.css" />';

		foreach($this->_getLanguages() as $language)
		{
			$relation = $this->_getLanguage($config, $language->lang_code);

			if($relation->translated || $language->lang_code == $original->original)
			{
				$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-success">' . substr($language->lang_code, 3, 5) . '</a></div>';
			}
			else
			{
				$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-important">' . substr($language->lang_code, 3, 5) . '</a></div>';
			}
		}

		return $html;
	}

	private function _getLanguages()
	{
		return $this->getService('com://admin/translations.model.languages')->getList();
	}

	private function _getLanguage($config, $language)
	{
		return $this->getService('com://admin/translations.model.translations')->row($config->row)->table($config->table)->lang($language)->getList()->top();
	}

	private function _getOriginalLanguage($config)
	{
		return $this->getService('com://admin/translations.model.translations')->row($config->row)->table($config->table)->getList()->top();
	}
}