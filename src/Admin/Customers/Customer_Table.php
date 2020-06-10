<?php

namespace SmartPay\Admin\Customers;

use SmartPay\Customers\DB_Customer;
use SmartPay\Customers\SmartPay_Customer;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Customer_Table extends \WP_List_Table
{
    /**
     * Number of items per page
     *
     * @var int
     * @since 0.0.1
     */
    public $per_page = 30;

    /**
     * Number of customers found
     *
     * @var int
     * @since 0.0.1
     */
    public $count = 0;

    /**
     * Total customers
     *
     * @var int
     * @since 0.0.1
     */
    public $total = 0;

    /**
     * The arguments for the data set
     *
     * @var array
     * @since 0.0.1
     */
    public $args = array();

    /**
     * Get things started
     *
     * @since 0.0.1
     * @see WP_List_Table::__construct()
     */
    public function __construct()
    {
        global $status, $page;

        // Set parent defaults
        parent::__construct(array(
            'singular' => __('Customer', 'smartpay'),
            'plural' => __('Customers', 'smartpay'),
            'ajax' => false,
        ));
    }

    /**
     * Show the search field
     *
     * @since 0.0.1
     *
     * @param string $text Label for the search box
     * @param string $input_id ID of the search box
     *
     * @return void
     */
    public function search_box($text, $input_id)
    {
        $input_id = $input_id . '-search-input';

        if (!empty(sanitize_text_field($_REQUEST['orderby'])))
            echo '<input type="hidden" name="orderby" value="' . esc_attr(sanitize_text_field($_REQUEST['orderby'])) . '" />';
        if (!empty(sanitize_text_field($_REQUEST['order'])))
            echo '<input type="hidden" name="order" value="' . esc_attr(sanitize_text_field($_REQUEST['order'])) . '" />';
?>
<p class="search-box">
    <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
    <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
    <?php submit_button($text, 'button', false, false, array('ID' => 'search-submit')); ?>
</p>
<?php
    }

    /**
     * Gets the name of the primary column.
     *
     * @since 0.0.1
     * @access protected
     *
     * @return string Name of the primary column.
     */
    protected function get_primary_column_name()
    {
        return 'name';
    }

    /**
     * This function renders most of the columns in the list table.
     *
     * @since 0.0.1
     *
     * @param array $item Contains all the data of the customers
     * @param string $column_name The name of the column
     *
     * @return string Column Name
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {

            case 'num_payments':
                $value = '<a href="' .
                    admin_url('/edit.php?post_type=download&page=smartpay-payment-history&user=' . urlencode($item['email'])) . '">' . esc_html($item['num_payments']) . '</a>';
                break;

                // case 'amount_spent':
                //     $value = smartpay_currency_filter(smartpay_format_amount($item[$column_name]));
                //     break;

            case 'created_at':
                $value = date_i18n(get_option('date_format'), strtotime($item['created_at']));
                break;

            default:
                $value = isset($item[$column_name]) ? $item[$column_name] : null;
                break;
        }
        return apply_filters('smartpay_customers_column_' . $column_name, $value, $item['id']);
    }

    public function column_name($item)
    {
        $name        = '#' . $item['id'] . ' ';
        $name       .= !empty($item['first_name'] && $item['last_name']) ? $item['first_name'] ?? '' . ' ' . $item['last_name'] ?? '' : 'Unnamed customer';
        // $user        = !empty($item['user_id']) ? $item['user_id'] : $item['email'];
        $customer    = new SmartPay_Customer($item['id']);
        $view_url    = admin_url('edit.php?post_type=download&page=smartpay-customers&view=overview&id=' . $item['id']);
        // $actions     = array(
        //     'view'   => '<a href="' . $view_url . '">' . __('View', 'smartpay') . '</a>',
        //     'logs'   => '<a href="' . admin_url('edit.php?post_type=download&page=smartpay-reports&tab=logs&customer=' . $customer->id) . '">' . __('Download log', 'smartpay') . '</a>',
        //     'delete' => '<a href="' . admin_url('edit.php?post_type=download&page=smartpay-customers&view=delete&id=' . $item['id']) . '">' . __('Delete', 'smartpay') . '</a>'
        // );

        $pending  = smartpay_user_pending_verification($customer->user_id) ? ' <em>' . __('(Pending Verification)', 'smartpay') . '</em>' : '';

        return '<a href="' . esc_url($view_url) . '">' . $name . '</a>' . $pending;
        //  . $this->row_actions($actions);
    }

    /**
     * Retrieve the table columns
     *
     * @since 0.0.1
     * @return array $columns Array of all the list table columns
     */
    public function get_columns()
    {
        $columns = array(
            'name'          => __('Name', 'smartpay'),
            'email'         => __('Email', 'smartpay'),
            'created_at'  => __('Date Created', 'smartpay'),
        );

        return apply_filters('smartpay_report_customer_columns', $columns);
    }

    /**
     * Get the sortable columns
     *
     * @since 0.0.1
     * @return array Array of all the sortable columns
     */
    public function get_sortable_columns()
    {
        return array(
            'created_at'  => array('created_at', true),
            'name'          => array('name', true),
        );
    }

    /**
     * Outputs the reporting views
     *
     * @since 0.0.1
     * @return void
     */
    public function bulk_actions($which = '')
    {
        // These aren't really bulk actions but this outputs the markup in the right place
    }

    /**
     * Retrieve the current page number
     *
     * @since 0.0.1
     * @return int Current page number
     */
    public function get_paged()
    {
        return isset($_GET['paged']) ? absint(sanitize_text_field($_GET['paged'])) : 1;
    }

    /**
     * Retrieves the search query string
     *
     * @since 0.0.1
     * @return mixed string If search is present, false otherwise
     */
    public function get_search()
    {
        return !empty($_GET['s']) ? urldecode(trim($_GET['s'])) : false;
    }

    /**
     * Build all the reports data
     *
     * @since 0.0.1
     * @global object $wpdb Used to query the database using the WordPress
     *   Database API
     * @return array $reports_data All the data for customer reports
     */
    public function reports_data()
    {
        global $wpdb;

        $data    = array();
        $paged   = $this->get_paged();
        $offset  = $this->per_page * ($paged - 1);
        $search  = $this->get_search();
        $order   = isset($_GET['order'])   ? sanitize_text_field($_GET['order'])   : 'DESC';
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';

        $args    = array(
            'number'  => $this->per_page,
            'offset'  => $offset,
            'order'   => $order,
            'orderby' => $orderby
        );

        if (is_email($search)) {
            $args['email'] = $search;
        } elseif (is_numeric($search)) {
            $args['id']    = $search;
        } elseif (strpos($search, 'user:') !== false) {
            $args['user_id'] = trim(str_replace('user:', '', $search));
        } else {
            $args['name']  = $search;
        }

        $this->args = $args;
        $customers  = (new DB_Customer)->get_customers($args);

        if ($customers) {

            foreach ($customers as $customer) {

                $user_id = !empty($customer->user_id) ? intval($customer->user_id) : 0;

                $data[] = array(
                    'id'            => $customer->ID,
                    'user_id'       => $user_id,
                    'first_name'    => $customer->first_name,
                    'last_name'     => $customer->last_name,
                    'email'         => $customer->email,
                    'created_at'  => $customer->created_at,
                );
            }
        }

        return $data;
    }

    /**
     * Setup the final data for the table
     *
     * @since 0.0.1
     * @uses SmartPay_Customer_Reports_Table::get_columns()
     * @uses WP_List_Table::get_sortable_columns()
     * @uses SmartPay_Customer_Reports_Table::get_pagenum()
     * @uses SmartPay_Customer_Reports_Table::get_total_customers()
     * @return void
     */
    public function prepare_items()
    {

        $columns  = $this->get_columns();
        $hidden   = array(); // No hidden columns
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $this->reports_data();

        $this->total = (new DB_Customer)->count($this->args);

        // Add condition to be sure we don't divide by zero.
        // If $this->per_page is 0, then set total pages to 1.
        $total_pages = $this->per_page ? ceil((int) $this->total / (int) $this->per_page) : 1;

        $this->set_pagination_args(array(
            'total_items' => $this->total,
            'per_page'    => $this->per_page,
            'total_pages' => $total_pages,
        ));
    }
}