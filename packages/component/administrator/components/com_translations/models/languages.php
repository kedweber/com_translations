<?php

class ComTranslationsModelLanguages extends ComDefaultModelDefault
{
	public function __construct(KConfig $config) {
		parent::__construct($config);

		$this->_state
			->insert('row', 'int')
			->insert('table', 'string')
			->insert('connect', 'boolean')
			->insert('sort', 'string', 'id')
			->insert('direction', 'string', 'asc')
		;
	}

	protected function _buildQueryColumns(KDatabaseQuery $query) {
		parent::_buildQueryColumns($query);

		if($this->_state->connect) {
			$query->select('trans.*');
			$query->select('rel.*');
		}
	}

	protected function _buildQueryJoins(KDatabaseQuery $query) {
		parent::_buildQueryJoins($query);

		if($this->_state->connect) {
			$query->join('cross', '#__translations_translations AS trans', '1 = 1');
			$query->join('left outer', '#__translations_translations_relations AS rel', 'trans.translations_translation_id = rel.translations_translation_id AND rel.lang = tbl.lang_code');
		}
	}

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

	    if($state->row && $state->table) {
		    $query->where('trans.row', '=', $state->row);
		    $query->where('trans.table', '=', $state->table);
	    }
    }
}