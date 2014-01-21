<?php

class ComTranslationsModelTranslations_relations extends ComDefaultModelDefault
{
	public function __construct(KConfig $config) {
		parent::__construct($config);

		$this->_state
			->insert('lang', 'string')
			->insert('translated', 'boolean')
			->insert('translations_translation_id', 'int')
		;
	}

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

	    if($state->translations_translation_id) {
		    $query->where('tbl.translations_translation_id', 'LIKE', $state->translations_translation_id);
	    }

	    if($state->translated) {
		    if($state->lang) {
			    $query->where('tbl.lang', 'LIKE', $state->lang);
		    }
		    $query->where('tbl.translated', '=', $state->translated);
	    }
    }
}