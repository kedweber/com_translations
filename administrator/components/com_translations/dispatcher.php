<?php

class ComTranslationsDispatcher extends ComDefaultDispatcher {
	public function _initialize(KConfig $config) {
		$config->append(array(
			'controller' => 'languages'
		));

		parent::_initialize($config);
	}
}