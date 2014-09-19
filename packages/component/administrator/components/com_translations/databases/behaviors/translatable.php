<?php

class ComTranslationsDatabaseBehaviorTranslatable extends KDatabaseBehaviorAbstract
{
	protected $_sync;
	protected $_recursive;

	/**
	 * @param KConfig $config
	 */
	public function __construct(KConfig $config)
	{
		if(isset($config->sync)) {
			$this->_sync = $config->sync;
		}

		if(isset($config->recursive)) {
			$this->_recursive = $config->recursive;
		}

		parent::__construct($config);
	}

    /**
     * @param KConfig $config
     */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority'	=> KCommand::PRIORITY_LOWEST,
			'recursive'	=> 0
        ));

        parent::_initialize($config);
    }

    /**
     * @param KCommandContext $context
     */
    protected function _beforeTableSelect(KCommandContext $context)
    {
        $iso_code       = substr(JFactory::getLanguage()->getTag(), 0, 2);
        $parent_table   = $context->caller;
        $query          = $context->query;
        $identityColumn = $parent_table->getIdentityColumn();

        if($query && $parent_table->getName() != 'cck_values' && $identityColumn) {
			$query->select('IF(translations.translated > 0, 1, 0) AS translated');
			$query->select('IF(translations.original > 0, 1, 0) AS original');
			$query->select('translations.iso_code AS language');

			$query->join('left', '#__translations_translations AS translations', array(
				'tbl.'.$identityColumn.' = translations.row',
				'translations.table = LOWER("'.strtoupper($parent_table->getBase()).'")',
				'translations.lang = LOWER("'.strtoupper(substr(JFactory::getLanguage()->getTag(), 0, 2)).'")'
			));
		}

        if($iso_code != 'en') {
            foreach($query->from as $key => $table) {

                $sanitized = strtok(str_replace('#__', $iso_code.'_', $table), " ");

                try {
                    if($parent_table->getDatabase()->getTableSchema($sanitized)) {
                        $query->from[$key] = str_replace('#__', '#__'.$iso_code.'_', $table);
                    }
                } catch(Exception $e) {
                    //TODO:: Mail error report!
                }
            }

            foreach($query->join as $key => $join) {
                try {
                    if($parent_table->getDatabase()->getTableSchema($iso_code.'_'.str_replace('#__', '', current(explode(' ', $join['table']))))) {
                        $query->join[$key]['table'] = str_replace('#__', '#__'.$iso_code.'_', $join['table']);
                    }
                } catch(Exception $e) {
                    //TODO:: Mail error report!
                }
            }
        }
    }

    /**
     * @param KCommandContext $context
     */
    protected function _beforeTableInsert(KCommandContext $context)
    {
        $iso_code   = substr(JFactory::getLanguage()->getTag(), 0, 2);
        $table      = $context->table;

        //TODO: Make this work via the database behavior.
        if(!$context->data->slug) {
            $filter = $this->getService('koowa:filter.slug');

            $context->data->slug = $filter->sanitize($context->data->title);
        }

        if($iso_code != 'en') {
            try {
                if($context->data->getTable()->getDatabase()->getTableSchema($iso_code.'_'.$table)) {
                    $context->table = $iso_code.'_'.$table;
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }
    }

    /**
     * @param KCommandContext $context
     */
    protected function _afterTableInsert(KCommandContext $context)
    {
        //TODO: Check in which table saved. Language wise.
        $table = $context->data->getTable();
        $database = $table->getDatabase();
        $languages	= $this->getLanguages();

        foreach($languages as $language) {
            $iso_code = $language->sef;

            $name = $this->getTableName($iso_code, $table);

            if($name == $context->table) {
                continue;
            }

            try {
                if($database->getTableSchema($name)) {
                    $query = $database->getQuery();

//                    $context->data->enabled = 0;

                    $data = $table->filter($context->data->getData(), true);
                    $data = $table->mapColumns($data);

                    $database->insert($name, $data, $query);
                } else {
                    var_dump($name);
                    die();
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }

		$this->_saveTranslation($context);
    }

    /**
     * @param KCommandContext $context
     */
    protected function _beforeTableUpdate(KCommandContext $context)
    {
        $iso_code   = substr(JFactory::getLanguage()->getTag(), 0, 2);
        $table      = $context->table;
        $modified = $context->data->getData(true);
        $language = JFactory::getLanguage();
        $original_lang = $language->getTag();
        $filter = $this->getService('koowa:filter.slug');

        if($modified['translated']) {
            // We have to get the original language.
            $row = $this->getService('com://admin/translations.database.row.translation');
            $row->setData(array(
                'table' => $table,
                'row' => $context->data->id,
                'original' => 1
            ))->load();

            $identifier = clone $context->data->getIdentifier();
            $identifier->name = KInflector::pluralize($identifier->name);
            $identifier->path = array('model');

            if($context->data->isSluggable() && !$row->isNew()) {
                $language->setLanguage($row->iso_code);
                $original = $this->getService($identifier)->id($context->data->id)->getItem();

                if($original->slug === $context->data->slug) {
                    $context->data->slug = $filter->sanitize($context->data->title);
                }
            }

            $language->setLanguage($original_lang);
        }

        if($iso_code != 'en') {
            try {
                if($context->data->getTable()->getDatabase()->getTableSchema($iso_code.'_'.$table)) {
                    $context->table = $iso_code.'_'.$table;
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }
    }

	protected function _afterTableUpdate(KCommandContext $context)
	{
		$this->_saveTranslation($context);

		if($this->_sync instanceof KConfig)
		{
			$this->_sync($context);
		}
	}

    /**
     * @param KCommandContext $context
     */
    protected function _beforeTableDelete(KCommandContext $context)
    {
        $iso_code   = substr(JFactory::getLanguage()->getTag(), 0, 2);
        $table      = $context->data->getTable();

        if($iso_code != 'en') {
            try {
                if($table->getDatabase()->getTableSchema($iso_code.'_'.$table->getBase())) {
                    $context->table = $iso_code.'_'.$table->getBase();
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }

	    if($context->data->getTable()->getName() && $context->data->id)
	    {
		    $this->getService('com://admin/translations.model.translations')->row($context->data->id)->table($context->data->getTable()->getName())->getList()->delete();
	    }
    }

    /**
     * @param KCommandContext $context
     */
    protected function _afterTableDelete(KCommandContext $context)
    {
        $table      = $context->data->getTable();
        $database   = $table->getDatabase();
        $languages	= $this->getLanguages();

        foreach($languages as $language) {
            $iso_code = $language->sef;

            $name = $this->getTableName($iso_code, $table);

            if($name == $context->table) {
                continue;
            }

            try {
                if($database->getTableSchema($name)) {
                    $database->delete($name, $context->query);
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }
    }

	/**
	 * @return array
	 */
	public function getLanguages()
	{
        $languages = JLanguageHelper::getLanguages();
        foreach($languages as $i => &$language) {
			if (!JLanguage::exists($language->lang_code)) {
				unset($languages[$i]);
				continue;
			}
		}

        return $languages;
    }

	/**
	 * @param $iso_code
	 * @param $table
	 * @return string
	 */
	public function getTableName($iso_code, $table)
    {
        $name = $table->getBase();

        if($iso_code != 'en') {
            try {
                if($table->getDatabase()->getTableSchema($iso_code.'_'.$name)) {
                    $name = $iso_code.'_'.$name;
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }

        return $name;
    }

	/**
	 * @param $context
	 */
	protected function _saveTranslation($context)
	{
		if($context->data->getTable()->getName() != 'cck_values')
		{
			$translation = $this->getService('com://admin/translations.database.row.translation');
			$translation->setData(array(
				'row'		=> $context->data->id,
				'table'		=>$context->data->getTable()->getBase(),
				'iso_code'	=> JFactory::getLanguage()->getTag()
			));
			$translation->load();
			$translation->setData(array(
				'translated' => $context->data->translated,
			));
			$translation->save();
		}
	}


	/**
	 * @param $context
	 */
	protected function _sync($context)
	{
        $original = JFactory::getLanguage()->getTag();

		if($this->_recursive == 0) {
			foreach($this->getLanguages() as $language) {
				$table = $context->data->getTable();

				if($language->sef != 'en') {
					try {
						if($context->data->getTable()->getDatabase()->getTableSchema($this->getTableName($language->sef, $table))) {
							JFactory::getLanguage()->setLanguage($language->lang_code);

							$identifier = clone $context->data->getIdentifier();
							$identifier->path = array('model');
							$identifier->name = KInflector::pluralize($identifier->name);

							$model = $this->getService($identifier);

							$row = $model->id($context->data->id)->getItem();

							if($behavior = $row->getTable()->getBehavior('translatable')) {
								$behavior->setRecursive(1);
							}

							foreach($this->_sync as $column) {
								$row->{$column} =  $context->data->{$column};
							}

							$row->save();
						}
					} catch(Exception $e) {
						//TODO:: Mail error report!
					}
				}
			}
		}

        // Reset back to original
        JFactory::getLanguage()->setLanguage($original);
	}

	public function setRecursive($value)
	{
		$this->_recursive = $value;
	}
}