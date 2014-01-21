<?php

class ComTranslationsDatabaseTableLanguages extends KDatabaseTableDefault
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'name'      => 'languages',
            'base'      => 'languages',
	        'identity_column' => 'lang_id'
        ));

        parent::_initialize($config);
    }
}