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
	public $_item;

    protected function _afterRead(KCommandContext $context)
    {
        if ($context->getError() && $context->getError()->getCode() === 404) {
            $originalApplicationLanguage = JFactory::getLanguage()->getTag();

            $id = $context->caller->getModel()->getState()->id;

            $originalArticleLanguage = $this->getService('com://admin/translations.model.translations')->table($this->getModel()->getTable()->getName())->row($id)->original(1)->getItem();

            if (!$originalArticleLanguage->isNew()) {
                $context->setError(null);

                // Load translation file for the message
                $lang   = JFactory::getLanguage();
                $lang->load('com_translations' , JPATH_ROOT.'/components/com_translations/', $lang->getTag(), true);

                // Get item from original language
                JFactory::getLanguage()->setLanguage($originalArticleLanguage->iso_code);
                $this->getModel()->reset();

				$context->result = $this->getModel()->id($id)->getItem();

				JFactory::getLanguage()->setLanguage($originalApplicationLanguage);

                // Display message
                JFactory::getApplication()->enqueueMessage(JText::_('ONLY_AVAILABLE_' . strtoupper(substr($context->result->language, 0, 2))), 'danger');
            }
        }
    }
}