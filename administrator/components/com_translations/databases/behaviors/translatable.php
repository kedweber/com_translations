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
}