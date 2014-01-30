<?php

class ComTranslationsDatabaseBehaviorTranslatable extends KDatabaseBehaviorAbstract
{
    /**
     * @param KConfig $config
     */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority'   => KCommand::PRIORITY_LOWEST
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
        // JH: If no query, exit function to avoid notices and possible memory issues.
        if(empty($query)) { return; }

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
        $filter = $this->getService('koowa:filter.slug');

        $context->data->slug = $filter->sanitize($context->data->title);

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

                    $context->data->enabled = 0;

                    $data = $table->filter($context->data->getData(), true);
                    $data = $table->mapColumns($data);

                    $database->insert($name, $data, $query);

	                // Save to the translations table.

	                if($context->data->id) {
		                $translation = $this->getService('com://admin/translations.database.row.translation');
		                $translation->setData(array(
			                'row' => $context->data->id,
			                'table' => ($iso_code != 'en') ? substr($name, 3) : $name,
			                'original' => JFactory::getLanguage()->getTag(),
		                ));

		                if(!$translation->load()) {
			                $translation->save();
		                }

		                if($language->lang_code != JFactory::getLanguage()->getTag())
		                {
			                $relation = $this->getService('com://admin/translations.database.row.translations_relation');
			                $relation->setData(array(
				                'translations_translation_id' => $translation->id,
				                'lang' => $language->lang_code
			                ));
			                $relation->save();
		                }
	                }
                }
            } catch(Exception $e) {
                //TODO:: Mail error report!
            }
        }
    }

    /**
     * @param KCommandContext $context
     */
    protected function _beforeTableUpdate(KCommandContext $context)
    {
        $iso_code   = substr(JFactory::getLanguage()->getTag(), 0, 2);
        $table      = $context->table;

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

	protected function _afterTableUpdate(KCommandContext $context) {
		if($context->data->getTable()->getName() != 'cck_values') {

			$translation = $this->getService('com://admin/translations.model.translations')->row($context->data->id)->table($context->data->getTable()->getName())->getList()->top();

			// We don't have an original yet. So we have to create it.
			if(!$translation->id)
			{
				$translation = $this->getService('com://admin/translations.database.row.translation');

				$translation->setData(array(
					'row' => $context->data->id,
					'table' => $context->data->getTable()->getName(),
					'original' => JFactory::getLanguage()->getTag()
				));

				$translation->save();
			}

			$relation = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->lang(JFactory::getLanguage()->getTag())->getList()->top();

			// We don't have a translation yet. So we have to create it.
			if(!$relation->id && $translation->original != JFactory::getLanguage()->getTag())
			{
				$relation = $this->getService('com://admin/translations.database.row.translations_relations');
				$relation->setData(array(
					'translations_translation_id' => $translation->id,
					'lang' => JFactory::getLanguage()->getTag(),
					'translated' => ($context->data->translated) ? 1 : 0
				));
				$relation->save();
			}
			else if($relation->id && $translation->original != JFactory::getLanguage()->getTag())
			{
				$relation->translated = ($context->data->translated) ? 1 : 0;
				$relation->save();
			}
		}

		return true;
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

	    if($context->data->getTable()->getName() != 'cck_values')
	    {
		    // I also have to remove the translations.
		    $rows = $this->getService('com://admin/translations.model.translations')->row($context->data->id)->table($context->data->getTable()->getName())->getList();
		    // We only need the first one (can only be one.)
		    $rows->top()->delete();
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

    public function getLanguages(){
        $languages = JLanguageHelper::getLanguages();
        foreach($languages as $i => &$language):
            // Do not display language without frontend UI
            if (!JLanguage::exists($language->lang_code)) :
                unset($languages[$i]);
                continue;
            endif;
        endforeach;
        return $languages;
    }

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

	public function translated()
	{
		$translated = true;
		$translation = $this->getService('com://admin/translations.model.translations')->row($this->id)->table($this->getTable()->getName())->getList()->top();

		if($translation->id && $translation->original != JFactory::getLanguage()->getTag())
		{
			$translated = false;

			$relation = $this->getService('com://admin/translations.model.translations_relations')->translations_translation_id($translation->id)->lang(JFactory::getLanguage()->getTag())->getList()->top();

			if($relation->translated)
			{
				$translated = true;
			}
		}
		else if(!$translation->id)
		{
			$translated = false;
		}

		return $translated;
	}
}