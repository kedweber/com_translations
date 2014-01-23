<?php

class ComTranslationsDatabaseTableTranslations extends KDatabaseTableDefault
{
	public function _initialize(KConfig $config)
	{
		$config->append(array(
			'behaviors' => array(
				'creatable',
				'modifiable',
				'lockable'
			)
		));

		parent::_initialize($config);
	}
}