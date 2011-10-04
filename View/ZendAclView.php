<?php

class ZendAclView extends View {


	protected function _paths($plugin = null, $cached = true) {
		$this->_paths = parent::_paths('ZendAcl', $cached);
		return array_reverse($this->_paths);
	}

}
