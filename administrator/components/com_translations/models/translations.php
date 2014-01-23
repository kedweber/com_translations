<?php

class ComTranslationsModelTranslations extends ComDefaultModelDefault
{
	public function __construct(KConfig $config) {
		parent::__construct($config);

		$this->_state
			->insert('lang', 'string')
			->insert('translated', 'boolean')
			->insert('row', 'int')
			->insert('table', 'string')
			->insert('original', 'string')
		;
	}

	protected function _buildQueryJoins(KDatabaseQuery $query) {
		parent::_buildQueryJoins($query);

		if($this->_state->lang) {
			$query->select('rel.*');
			$query->join('left outer', '#__translations_translations_relations AS rel', 'tbl.translations_translation_id = rel.translations_translation_id');
		}
	}

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

	    if($state->original) {
		    $query->where('original', 'LIKE', $state->original);
	    }

	    if($state->lang) {
			$query->where('rel.lang', '=', $state->lang);
	    }

	    if($state->row && $state->table) {
		    $query->where('row', '=', $state->row);
		    $query->where('table', '=', $state->table);
	    }
    }
}