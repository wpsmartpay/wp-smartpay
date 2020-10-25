<?php

namespace SmartPay\Models;

abstract class Model {

    public $ID = 0;
	protected $_ID = 0;

    protected $created_at = '';

	protected $updated_at = '';

	protected $new = false;

	protected $pending = [];

    protected $status = 'publish';

    abstract protected function get_meta($meta_key = '', $single = true);

	abstract protected function update_meta($meta_key = '', $meta_value = '', $prev_value = '');

	public function __set($key, $value): void
    {
        if (!in_array($key, ['_ID'])) {
            $this->pending[$key] = $value;
            $this->$key = $value;
        }
	}

    public function delete(): bool
    {
        return wp_delete_post($this->_ID);
    }

}
