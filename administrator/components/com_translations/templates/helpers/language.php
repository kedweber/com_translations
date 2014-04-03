<?php

class ComTranslationsTemplateHelperLanguage extends KTemplateHelperAbstract
{
	public function translations($config = array())
	{
		$config = new KConfig($config);
		$config->append(array(
			'row' => null,
			'table' => '',
		));

		// First for our knowledge we get the original language (if exists.)
		$original = $this->_getOriginalLanguage($config->row, $config->table);
		$html = '<style src="media://com_translations/css/translations.css" />';
        $view = KInflector::singularize(KRequest::get('get.view', 'string'));

		foreach($this->_getLanguages() as $language)
		{
			$relation = $this->_getLanguage($config, $language->lang_code);

			if($language->lang_code == $original->iso_code) {
				$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=' . $view . '&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-info">' . strtoupper(substr($language->lang_code, 0, 2)) . '</a></div>';
			}
			else if($relation->translated)
			{
				$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=' . $view . '&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-success">' . strtoupper(substr($language->lang_code, 0, 2)) . '</a></div>';
			}
			else
			{
				if(strtotime('+ 2 weeks', strtotime($original->created_on)) > strtotime(date('d-m-Y H:i:s')))
				{
					$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=' . $view . '&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-warning">' . strtoupper(substr($language->lang_code, 0, 2)) . '</a></div>';
				}
				else if(strtotime('+ 2 weeks', strtotime($original->created_on)) < strtotime(date('d-m-Y H:i:s')))
				{
					$html .= ' <a href="' . $this->getTemplate()->getView()->createRoute('view=' . $view . '&id=' . $config->row . '&backendlanguage=' . $language->lang_code) . '"><div class="badge badge-important">' . strtoupper(substr($language->lang_code, 0, 2)) . '</a></div>';
				}
			}
		}

		return $html;
	}

    private function _getLanguages() {
        return $this->getService('com://admin/translations.model.languages')->getList();
    }

	private function _getOriginalLanguage($row, $table)
	{
		$row = $this->getService('com://admin/translations.database.row.translation')->setData(array(
            'row' => $row,
            'table' => $table,
            'original' => 1
        ))->load();

        if($row->id) {
            return $row;
        }
	}

	private function _getLanguage($config, $language)
	{
        $row = $this->getService('com://admin/translations.model.translations')->row($config->row)->table($config->table)->iso_code($language)->original(0)->getList();

		if($row instanceof KDatabaseRowsetDefault) {
            return $row->top();
        }
	}
}