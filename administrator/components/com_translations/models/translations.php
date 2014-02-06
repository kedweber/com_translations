<?php

class ComTranslationsModelTranslations extends ComDefaultModelDefault
{
	/**
	 * @param KConfig $config
	 */
	public function __construct(KConfig $config)
	{
		parent::__construct($config);

		$this->_state
			->insert('lang', 'string')
			->insert('translated', 'boolean')
			->insert('original', 'boolean')
		;
	}

	/**
	 * @param KDatabaseQuery $query
	 */
	protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->_state;

        parent::_buildQueryWhere($query);

	    if(is_numeric($state->original)) {
		    $query->where('original', '=', $state->original);
	    }

        if($state->lang) {
            $query->where('iso_code', 'LIKE', $state->lang);
        }
    }
}