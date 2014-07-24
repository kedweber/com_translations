<?php

/**
 * 
 *
 * @author 		Joep van der Heijden <joep.van.der.heijden@moyoweb.nl>
 */
 
defined('KOOWA') or die('Restricted Access');

class ComTranslationsTemplateHelperLanguages extends ComDefaultTemplateHelperBehavior
{
    public function original($config)
    {
        $config = new KConfig($config);
        $config->append(array('attribs' => array('class' => 'original-lang')));
        $item = $config->item;
        $html = '';

        if ($item->isTranslatable() && !$item->translated) {
            $original = $this->getService('com://admin/translations.model.translations')->table($item->getTable()->getName())->row($item->id)->original(1)->getItem();
            if ($original->iso_code != $item->language && $original->id) {
                $html .= '<span ' . KHelperArray::toString($config->attribs) .'>' . $this->translate('ONLY_AVAILABLE_' . strtoupper($original->lang) . '_SHORT') . '</span>';
            }
        }

        return $html;
    }
}