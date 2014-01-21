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

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

	    if($state->original) {
		    $query->where('original', 'LIKE', $state->original);
	    }

	    if($state->translated) {
		    if($state->lang) {
			    $query->where('lang', 'LIKE', $state->lang);
		    }
		    $query->where('translated', '=', $state->translated);
	    }

	    if($state->row && $state->table) {
		    $query->where('row', '=', $state->row);
		    $query->where('table', '=', $state->table);
	    }
    }
}