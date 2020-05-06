<?php

namespace SmartPay\Downloads;

use SmartPay\Model;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

class SmartPay_Download extends Model
{
	/**
	 * The download ID
	 *
	 * @since  0.1
	 * @var    integer
	 */
	public    $ID  = 0;
	protected $_ID = 0;

	/**
	 * The status of the download
	 *
	 * @since  0.1
	 * @var string
	 */
	protected $status      = 'pending';
	protected $post_status = 'pending'; // Same as $status but here for backwards compat

	/**
	 * The display name of the current download status
	 *
	 * @since  0.1
	 * @var string
	 */
	protected $status_nicename = '';

	/**
	 * The price the download
	 *
	 * @since  0.1
	 * @var float
	 */
	protected $price = 0.00;

	/**
	 * The title of the payee
	 *
	 * @since  0.1
	 * @var string
	 */
	protected $title = '';

	/**
	 * The description of the payee
	 *
	 * @since  0.1
	 * @var string
	 */
	protected $description = '';

	/**
	 * The image used for the download
	 *
	 * @since  0.1
	 * @var string
	 */
	protected $image = '';

	/**
	 * Identify if the download is a new one or existing
	 *
	 * @since  0.1
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * When updating, the old status prior to the change
	 *
	 * @since  0.1
	 * @var string
	 */
	protected $old_status = '';

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_download_meta calls to be run
	 *
	 * @since  0.1
	 * @var array
	 */
	private $pending;

	/**
	 * Setup the smartpay downloads class
	 *
	 * @since 0.1
	 * @param int $download_id A given download
	 * @return mixed void|false
	 */
	public function __construct($download_or_txn_id = false, $by_txn = false)
	{
		global $wpdb;

		if (empty($download_or_txn_id)) {
			return false;
		}

		if ($by_txn) {
			$query      = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_smartpay_download_transaction_id' AND meta_value = '%s'", $download_or_txn_id);
			$download_id = $wpdb->get_var($query);

			if (empty($download_id)) {
				return false;
			}
		} else {
			$download_id = absint($download_or_txn_id);
		}

		$this->setup_download($download_id);
	}

	/**
	 * Magic GET function
	 *
	 * @since  0.1
	 * @param  string $key  The property
	 * @return mixed        The value
	 */
	public function __get($key)
	{
		if (method_exists($this, 'get_' . $key)) {

			$value = call_user_func(array($this, 'get_' . $key));
		} else {

			$value = $this->$key;
		}

		return $value;
	}

	/**
	 * Magic SET function
	 *
	 * Sets up the pending array for the save method
	 *
	 * @since  0.1
	 * @param string $key   The property name
	 * @param mixed $value  The value of the property
	 */
	public function __set($key, $value)
	{
		$ignore = array('_ID');

		if ($key === 'status') {
			$this->old_status = $this->status;
		}

		if (!in_array($key, $ignore)) {
			$this->pending[$key] = $value;
		}

		if ('_ID' !== $key) {
			$this->$key = $value;
		}
	}

	/**
	 * Setup download properties
	 *
	 * @since  0.1
	 * @param  int  $download_id The download ID
	 * @return bool If the setup was successful or not
	 */
	private function setup_download($download_id)
	{
		if (empty($download_id)) {
			return false;
		}

		$download = get_post($download_id);
		if (!$download || is_wp_error($download)) {
			return false;
		}

		if ('smartpay_download' !== $download->post_type) {
			return false;
		}

		// Primary Identifier
		$this->ID               = absint($download_id);

		// Protected ID that can never be changed
		$this->_ID              = absint($download_id);

		$this->form_id          = $this->setup_form_id();

		// Status and Dates
		$this->date             = $download->post_date;
		$this->completed_date   = $this->setup_completed_date();
		$this->status           = $download->post_status;
		$all_download_statuses   = smartpay_get_download_statuses();
		$this->status_nicename  = array_key_exists($this->status, $all_download_statuses) ? $all_download_statuses[$this->status] : ucfirst($this->status);

		$this->amount           = $this->setup_amount();
		$this->currency         = $this->setup_currency();
		$this->download_gateway  = $this->setup_download_gateway();
		$this->transaction_id   = $this->setup_transaction_id();

		$this->first_name       = $this->setup_first_name();
		$this->last_name        = $this->setup_last_name();
		$this->email            = $this->setup_email();

		// Other Identifiers
		$this->key              = $this->setup_download_key();

		$this->parent_download   = $download->post_parent;

		$this->mode             = $this->setup_mode();

		return true;
	}

	/**
	 * One items have been set, an update is needed to save them to the database.
	 *
	 * @return bool  True of the save occurred, false if it failed or wasn't needed
	 */
	public function save()
	{
		$saved = false;

		if (empty($this->ID)) {

			$download_id = $this->insert_download();

			if (false === $download_id) {
				$saved = false;
			} else {
				$this->ID = $download_id;
			}
		}

		if ($this->ID !== $this->_ID) {
			$this->ID = $this->_ID;
		}

		// If we have something pending, let's save it
		if (!empty($this->pending)) {

			foreach ($this->pending as $key => $value) {
				switch ($key) {
					case 'form_id':
						$this->update_meta('_smartpay_download_form_id', $this->form_id);
						break;

					case 'date':
						$args = array(
							'ID'        => $this->ID,
							'post_date' => $this->date,
							'edit_date' => true,
						);

						wp_update_post($args);
						break;

					case 'completed_date':
						$this->update_meta('_smartpay_download_completed_date', $this->completed_date);
						break;

					case 'status':
						$this->update_status($this->status);
						break;

					case 'amount':
						$this->update_meta('_smartpay_download_amount', $this->amount);
						break;

					case 'currency':
						$this->update_meta('_smartpay_download_currency', $this->currency);
						break;

					case 'download_gateway':
						$this->update_meta('_smartpay_download_gateway', $this->download_gateway);
						break;

					case 'transaction_id':
						$this->update_meta('_smartpay_download_transaction_id', $this->transaction_id);
						break;

					case 'first_name':
						$this->update_meta('_smartpay_download_first_name', $this->first_name);
						break;

					case 'last_name':
						$this->update_meta('_smartpay_download_last_name', $this->last_name);
						break;

					case 'email':
						$this->update_meta('_smartpay_download_email', $this->email);
						break;

					case 'key':
						$this->update_meta('_smartpay_download_key', $this->key);
						break;

					case 'parent_download':
						$args = array(
							'ID'          => $this->ID,
							'post_parent' => $this->parent_download,
						);

						wp_update_post($args);
						break;

					case 'mode':
						$this->update_meta('_smartpay_download_mode', $this->mode);
						break;

					default:
						/**
						 * Used to save non-standard data. Developers can hook here if they want to save
						 * specific download data when $download->save() is run and their item is in the $pending array
						 */
						do_action('smartpay_download_save', $this, $key);
						break;
				}
			}

			$this->pending = array();
			$saved         = true;
		}

		if (true === $saved) {
			$this->setup_download($this->ID);

			/**
			 * This action fires anytime that $download->save() is run, allowing developers to run actions
			 * when a download is updated
			 */
			do_action('download_data_download_saved', $this->ID, $this);
		}

		/**
		 * Update the download in the object cache
		 */
		// $cache_key = md5('download_data_download' . $this->ID);
		// wp_cache_set($cache_key, $this, 'downloads');

		return $saved;
	}

	/**
	 * Create the base of a download.
	 *
	 * @since  0.1
	 * @return int|bool False on failure, the download ID on success.
	 */
	private function insert_download()
	{
		if (empty($this->key)) {
			$this->key = strtolower(md5($this->email . date('Y-m-d H:i:s') . rand(1, 10)));  // Unique key
			$this->pending['key'] = $this->key;
		}

		// Create a blank download
		$download_id = wp_insert_post(array(
			'post_type'      => 'smartpay_download',
			'post_status'    => 'pending',
			'post_date'      => !empty($this->date) ? $this->date : null,
			'post_date_gmt'  => !empty($this->date) ? get_gmt_from_date($this->date) : null,
			'post_parent'    => $this->parent_download,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		));

		if (!empty($download_id)) {
			$this->ID   = $download_id;
			$this->_ID  = $download_id;

			$this->new  = true;
		}

		return $this->ID;
	}

	/**
	 * Set the download status and run any status specific changes necessary
	 *
	 * @since 0.1
	 *
	 * @param  string $status The status to set the download to
	 * @return bool Returns if the status was successfully updated
	 */
	public function update_status($status = false)
	{
		if ($status == 'completed' || $status == 'complete') {
			$status = 'publish';
		}

		$old_status = !empty($this->old_status) ? $this->old_status : false;

		if ($old_status === $status) {
			return false; // Don't permit status changes that aren't changes
		}

		$updated = false;

		do_action('smartpay_before_download_status_change', $this->ID, $status, $old_status);

		$update_fields = array('ID' => $this->ID, 'post_status' => $status, 'edit_date' => current_time('mysql'));

		$updated = wp_update_post(apply_filters('smartpay_update_download_status_fields', $update_fields));

		$this->status = $status;
		$this->post_status = $status;

		$all_download_statuses  = smartpay_get_download_statuses();
		$this->status_nicename = array_key_exists($status, $all_download_statuses) ? $all_download_statuses[$status] : ucfirst($status);

		// Process any specific status functions
		// switch ($status) {
		//     case 'refunded':
		//         $this->process_refund();
		//         break;
		//     case 'failed':
		//         $this->process_failure();
		//         break;
		//     case 'pending' || 'processing':
		//         $this->process_pending();
		//         break;
		// }

		do_action('smartpay_update_download_status', $this->ID, $status, $old_status);

		return $updated;
	}

	/**
	 * Get a post meta item for the download
	 *
	 * @since  0.1
	 * @param  string   $meta_key The Meta Key
	 * @param  boolean  $single   Return single item or array
	 * @return mixed    The value from the post meta
	 */
	public function get_meta($meta_key = '', $single = true)
	{
		if (empty($meta_key)) {
			return;
		}

		$meta = get_post_meta($this->ID, $meta_key, $single);

		$meta = apply_filters('smartpay_get_download_meta_' . $meta_key, $meta, $this->ID);

		if (is_serialized($meta)) {
			preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
			if (!empty($matches)) {
				$meta = array();
			}
		}

		return apply_filters('smartpay_get_download_meta', $meta, $this->ID, $meta_key);
	}

	/**
	 * Update the post meta
	 *
	 * @since  0.1
	 * @param  string $meta_key   The meta key to update
	 * @param  string $meta_value The meta value
	 * @param  string $prev_value Previous meta value
	 * @return int|bool           Meta ID if the key didn't exist, true on successful update, false on failure
	 */
	public function update_meta($meta_key = '', $meta_value = '', $prev_value = '')
	{
		if (empty($meta_key)) {
			return;
		}

		$meta_value = apply_filters('smartpay_update_download_meta_' . $meta_key, $meta_value, $this->ID);

		return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
	}

	/**
	 * Add an item to the download meta
	 *
	 * @since 2.8
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param bool   $unique
	 *
	 * @return bool|false|int
	 */
	public function add_meta($meta_key = '', $meta_value = '', $unique = false)
	{
		if (empty($meta_key)) {
			return false;
		}

		return add_post_meta($this->ID, $meta_key, $meta_value, $unique);
	}

	/**
	 * Delete an item from download meta
	 *
	 * @since 2.8
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool
	 */
	public function delete_meta($meta_key = '', $meta_value = '')
	{
		if (empty($meta_key)) {
			return false;
		}

		return delete_post_meta($this->ID, $meta_key, $meta_value);
	}

	/**
	 * Setup the user info
	 *
	 * @since  0.1
	 * @return array The user info associated with the download
	 */
	private function setup_form_id()
	{
		return $this->get_meta('_smartpay_download_form_id', true);
	}

	/**
	 * Setup the download completed date
	 *
	 * @since  0.1
	 * @return string The date the download was completed
	 */
	private function setup_completed_date()
	{
		$download = get_post($this->ID);

		if ('pending' == $download->post_status || 'preapproved' == $download->post_status || 'processing' == $download->post_status) {
			return false; // This download was never completed
		}

		$date = ($date = $this->get_meta('_smartpay_download_completed_date', true)) ? $date : $download->date;

		return $date;
	}

	/**
	 * Setup the download amount
	 *
	 * @since  0.1
	 * @return float The download amount
	 */
	private function setup_amount()
	{
		return $this->get_meta('_smartpay_download_amount', true);
	}

	/**
	 * Setup the currency code
	 *
	 * @since  0.1
	 * @return string The currency for the download
	 */
	private function setup_currency()
	{
		return $this->get_meta('_smartpay_download_currency', true) ?? smartpay_get_currency();
	}

	/**
	 * Setup the download gateway
	 *
	 * @since  0.1
	 * @return string The download gateway
	 */
	private function setup_download_gateway()
	{
		return $this->get_meta('_smartpay_download_gateway');
	}

	/**
	 * Setup the transaction ID
	 *
	 * @since  0.1
	 * @return string The transaction ID for the download
	 */
	private function setup_transaction_id()
	{
		$transaction_id = $this->get_meta('_smartpay_download_transaction_id', true);

		if (empty($transaction_id) || (int) $transaction_id === (int) $this->ID) {

			$gateway        = $this->gateway;
			$transaction_id = apply_filters('smartpay_get_download_transaction_id-' . $gateway, $this->ID);
		}

		return $transaction_id;
	}

	/**
	 * Setup the first_name for the purchase
	 *
	 * @since  0.1
	 * @return string The email address for the download
	 */
	private function setup_first_name()
	{
		return  $this->get_meta('_smartpay_download_first_name', true);
	}

	/**
	 * Setup the last_name for the purchase
	 *
	 * @since  0.1
	 * @return string The email address for the download
	 */
	private function setup_last_name()
	{
		return  $this->get_meta('_smartpay_download_last_name', true);
	}

	/**
	 * Setup the email address for the purchase
	 *
	 * @since  0.1
	 * @return string The email address for the download
	 */
	private function setup_email()
	{
		return  $this->get_meta('_smartpay_download_email', true);
	}

	/**
	 * Setup the download key
	 *
	 * @since  0.1
	 * @return string The download Key
	 */
	private function setup_download_key()
	{
		return $this->get_meta('_smartpay_download_key', true);
	}

	/**
	 * Setup the download mode
	 *
	 * @since  0.1
	 * @return string The download mode
	 */
	private function setup_mode()
	{
		return $this->get_meta('_smartpay_download_mode');
	}


	public function complete_download()
	{
		return $this->update_status('completed');
	}
}