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
			->insert('original', 'boolean')
		;
	}

	protected function _buildQueryJoins(KDatabaseQuery $query) {
		parent::_buildQueryJoins($query);
	}

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

	    if($state->original) {
		    $query->where('original', '=', $state->original);
	    }

        if($state->lang) {
            $query->where('iso_code', 'LIKE', $state->lang);
        }

	    if($state->row && $state->table) {
		    $query->where('row', '=', $state->row);
		    $query->where('table', '=', $state->table);
	    }
    }
}