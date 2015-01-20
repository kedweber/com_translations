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
            ->insert('table', 'string')
            ->insert('row', 'int')
			->insert('translated', 'boolean')
			->insert('original', 'boolean', null ,true)
		;
	}

	/**
	 * @param KDatabaseQuery $query
	 */
	protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->_state;

        parent::_buildQueryWhere($query);

		if($state->row && $state->table) {
			$query->where('tbl.row', '=', $state->row);
			$query->where('tbl.table', '=', $state->table);
		}
    }
}