<?php

class ComTranslationsModelTranslations extends ComDefaultModelDefault
{
    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

        //Forced filtering!
        $query->where('tbl.row', '=', $state->row);
        $query->where('tbl.table', '=', $state->table);
        $query->where('tbl.language_code', '=', $state->language_code);
    }
}