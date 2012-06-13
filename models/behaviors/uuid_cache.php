<?php
/**
 * Uuid Cache Behavior
 *
 * Behavior for making cache calls generic in the model layer. Assumes that the
 * passed in ID is uuid.
 *
 * Things to change:
 *   Different method for handling directory building
 *   Support for cache configs other than the default (partially implemented)
 */
App::import('Core', 'Folder');

class UuidCacheBehavior extends ModelBehavior {

	public $settings = array();

	public $_base = '';

	public $_config = 'default';

	public $_cacheDir = 'uuid';

	public $_Folder = null;

	/**
	 * Set setting per model that use this behavior
	 *
	 * @param object $model
	 * @param array $config
	 * @access public
	 * @return void
	 */
	public function setup(&$model, $config = array()) {
		$default = array('enabled' => true);
		$this->settings[$model->alias] = Set::merge($default, $config);
		$this->_base = APP.'tmp'.DS.'cache'.DS.$this->_cacheDir.DS;
	}

	/**
	 * Method used to handle reading and writing of cache. If data param
	 * is anything but false, then the cache will be written, otherwise
	 * cache will be returned.
	 *
	 * @param object $model
	 * @param int $id
	 * @param string $file
	 * @param mixed $data
	 * @access public
	 * @return mixed
	 */
	public function cache(&$model, $id = null, $file = null, $data = false) {
		if ($this->settings[$model->alias]['enabled']) {
			$path = $this->_path($id);
			$config = $this->_config($model);
			Cache::set(array('path' => $path));
			if ($data === false) {
				$cache = Cache::read($file);
				if ($cache) {
					return $cache;
				}
				return array();
			} else {
				return Cache::write($file, $data);
			}
		} else {
			return false;
		}
	}

	/**
	 * With the UUID, create the directory structure for the cache file
	 *
	 * @param string $id
	 * @access public
	 * @return string
	 */
	public function _path($id = null) {
		if ($this->_Folder === null) {
			$this->_Folder = new Folder();
		}
		if (strpos($id, "-") !== false) {
			$path = $this->_base . implode(DS, explode('-', $id));
		} else {
			$path = $this->_base . implode(DS, str_split($id, 8));
		}
		if ($this->_Folder->create($path, 0777)) {
			return $path;
		}
		return false;
	}

	/**
	 * Checks for a config assigned to the model->alias in the settings or
	 * returns the default $this->_config
	 *
	 * @param object $model
	 * @access public
	 * @return string
	 */
	public function _config(&$model) {
		$config = $this->_config;
		if (isset($this->settings[$model->alias]['config']) && !empty($this->settings[$model->alias]['config'])) {
			$config = $this->settings[$model->alias]['config'];
		}
		return $config;
	}

}

?>