<?php

class ComTranslationsTemplateHelperLanguage extends KTemplateHelperAbstract {
	public function translations($config = array()) {
		$config = new KConfig($config);
		$config->append(array(
			'row' => null,
			'table' => '',
		));

		$languages = $this->getService('com://admin/translations.model.languages')->getList();

		$translation = $this->getService('com://admin/translations.model.translations')->row($config->row)->table($config->table)->getList()->top();
		$relations = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->getList();

		// The original is always translated.
		$html = '';

		if($translation) {
			$html .= '<style src="media://com_translations/css/translations.css" />';
			$html .= '<a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $translation->original) . '"><div class="badge badge-success">' . substr($translation->original, 3, 5) . '</a></div>';

			foreach($languages as $language)
			{
				if($language->lang_code != $translation->original)
				{
					$relation = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->lang($language->lang_code)->getList()->top();
					if(!$relation->id)
					{
						$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-important">' . substr($language->lang_code, 3, 5) . '</a></div>';
					}
					else
					{
						if($relation->translated)
						{
							$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $relation->lang) . '"><div class="badge badge-success">' . substr($relation->lang, 3, 5) . '</a></div>';
						}
						else
						{
							$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=article&id=' . $config->row . '&backendlanguage=' . $relation->lang) . '"><div class="badge badge-important">' . substr($relation->lang, 3, 5) . '</a></div>';
						}
					}
				}
			}
		}

		return $html;
	}
}