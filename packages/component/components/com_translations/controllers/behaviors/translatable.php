<?php

/**
 * ComArticles
 *
 * @author 		Joep van der Heijden <joep.van.der.heijden@moyoweb.nl>
 * @category
 * @package
 * @subpackage
 */

 defined('KOOWA') or die('Restricted Access');

class ComTranslationsControllerBehaviorTranslatable extends KControllerBehaviorAbstract
{
    private function __loadLanguageFile($language)
    {
        $language->load('com_translations' , JPATH_ROOT.'/components/com_translations/', $language->getTag(), true);
    }

    /**
     * @return string
     * @sameAs ComTranslationsDatabaseBehaviorTranslatable::getTableName
     */
    private function __getTableName($iso_code, $table)
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
     * Resolve the ID of the item. If it's not in the state, get it with a DB query.
     *
     * @param $currentLanguageTag
     * @return int
     */
    private function __resolveId($currentLanguageTag)
    {
        $id = $this->getModel()->getState()->id;

        if (!$id && $this->getModel()->getState()->isUnique()) {
            // id is not set in the state because it's not in the url and could not be parsed. Resolve id from query with unique statements.
            // Note: admin identifier will not be possible, see: https://groups.google.com/forum/#!topic/nooku-framework/dBOTd7KLu-s
            $state = $this->getModel()->getState();

            $table = $this->getModel()->getTable();
            $query = clone $table->getDatabase()->getQuery();
            $identityColumn = $table->getIdentityColumn();

            $query->select($identityColumn);
            // From the correct table (with language tag)
            $query->from($this->__getTableName(substr($currentLanguageTag, 0, 2), $table) . ' AS tbl');

            foreach($state as $key => $value) {
                if ($value->unique && $value->value) {
                    $query->where('tbl.'.$key, '=', $value->value);
                }
            }

            $dbo = JFactory::getDbo();
            $dbo->setQuery($query->__toString());
            $result = $dbo->loadObject();

            $id = $result->$identityColumn;
        }

        return $id;
    }

    /**
     * @param KCommandContext $context
     * @return null
     */
    protected function _getItemInOriginalLanguage(KCommandContext $context)
    {
        // Get current language of the application
        $originalApplicationLanguage = JFactory::getLanguage()->getTag();

        $id = $this->__resolveId($originalApplicationLanguage);

        if (!$id) {
            return null;
        }

        // Get the original language of the item
        $originalItemLanguage = $this->getService('com://admin/translations.model.translations')->table($this->getModel()->getTable()->getName())->row($id)->original(1)->getItem();

        if (!$originalItemLanguage->isNew()) {
            // Get item from the original language
            JFactory::getLanguage()->setLanguage($originalItemLanguage->iso_code);
            $this->getModel()->reset();
            $itemInOriginalLanguage = $this->getModel()->id($id)->getItem();
            JFactory::getLanguage()->setLanguage($originalApplicationLanguage);

            if (!$itemInOriginalLanguage->isNew()) {
                return $itemInOriginalLanguage;
            }
        }

        return null;
    }

    /**
     * If item is not translated, display the item in the original language with a warning message.
     * If item is not enabled, display the item in the original language (if enabled) with a warning message.
     * Note: only works in html format
     *
     * @param KCommandContext $context
     */
    protected function _afterRead(KCommandContext $context)
    {
        if ($this->format == 'html') {
            if ($context->getError() && $context->getError()->getCode() === 404 || $context->result->translated == 0) {
                $itemInOtherLanguage = $this->_getItemInOriginalLanguage($context);

                if ($itemInOtherLanguage != null) {
                    // Forbid search engines to index the page
                    JFactory::getDocument()->setMetaData('robots','noindex');

                    // Remove the error so there won't be a 404 response
                    $context->setError(null);

                    $context->result = $itemInOtherLanguage;

                    // Display the message
                    $this->__loadLanguageFile(JFactory::getLanguage());
                    JFactory::getApplication()->enqueueMessage(JText::_('ONLY_AVAILABLE_' . strtoupper(substr($itemInOtherLanguage->language, 0, 2))), 'danger');
                }

            }
        }
    }
}