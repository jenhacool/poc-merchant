<?php

namespace App;

use App\Utilities\SingletonTrait;
use App\Utilities\Helpers;

class POC_Merchant
{
    use SingletonTrait;

    public $ajax;

    protected function __construct()
    {
        $this->init_classes();

        $this->add_hooks();
    }

    protected function init_classes()
    {
        $this->ajax = new POC_Merchant_AJAX();
    }

    /**
     * Add hooks
     */
    protected function add_hooks()
    {
        add_filter( 'woocommerce_checkout_fields', array( $this, 'modify_checkout_fields' ) );

        add_action( 'wp_ajax_poc_merchant_load_address', array( $this->ajax, 'load_address' ) );

        add_action( 'wp_ajax_nopriv_poc_merchant_load_address', array( $this->ajax, 'load_address' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_filter( 'woocommerce_localisation_address_formats', array( $this, 'localisation_address_formats' ), 9999 );

        add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'order_formatted_billing_address' ), 10, 2 );

        add_filter( 'default_checkout_billing_country', array( $this, 'set_default_checkout_country' ), 9999 );

        add_filter( 'woocommerce_get_order_address', array( $this, 'get_order_address' ), 99, 2 );

        add_filter( 'woocommerce_add_to_cart_product_id', array( $this, 'set_default_variation_id' ) );

        add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'modify_cart_item_quantity' ), 10, 3 );

        add_action( 'wp_ajax_poc_merchant_update_cart_on_checkout', array( $this->ajax, 'update_cart_on_checkout' ) );

        add_action( 'wp_ajax_nopriv_poc_merchant_update_cart_on_checkout', array( $this->ajax, 'update_cart_on_checkout' ) );

        add_action( 'wp_ajax_poc_merchant_product_search', array( $this->ajax, 'product_search' ) );

        add_action( 'wp_ajax_nopriv_poc_merchant_product_search', array( $this->ajax, 'product_search' ) );

        add_action( 'woocommerce_new_order', array( $this, 'add_order_meta_data' ), 10, 2 );
    }

    public function modify_checkout_fields( $fields )
    {
        require_once POC_MERCHANT_PLUGIN_DIR . '/assets/data/tinh_thanhpho.php';

        $fields['billing']['billing_state'] = array(
            'label'	=> __('Province/City', 'poc-merchant'),
            'required' => true,
            'type' => 'select',
            'class' => array( 'form-row-first', 'address-field' ),
            'placeholder' => _x( 'Select Province/City', 'placeholder', 'poc-merchant' ),
            'options' => array( '' => __( 'Select Province/City', 'poc-merchant' ) ) + $tinh_thanhpho,
            'priority'  =>  30
        );

        $fields['billing']['billing_city'] = array(
            'label'	=> __('District', 'poc-merchant'),
            'required' => true,
            'type' => 'select',
            'class' => array( 'form-row-last', 'address-field' ),
            'placeholder' =>	_x( 'Select District', 'placeholder', 'poc-merchant' ),
            'options' => array(
                ''	=> ''
            ),
            'priority'  =>  40
        );

        $fields['billing']['billing_address_2'] = array(
            'label' => __( 'Commune/Ward/Town', 'poc-merchant' ),
            'required' => true,
            'type' => 'select',
            'class' => array( 'form-row-first', 'address-field' ),
            'placeholder' => _x( 'Select Commune/Ward/Town', 'placeholder', 'poc-merchant' ),
            'options' => array(
                '' => ''
            ),
            'priority'  =>  50
        );

        $fields['billing']['billing_address_1']['class'] = array( 'form-row-last' );
        $fields['billing']['billing_address_1']['priority']  = 60;

        $fields['billing']['billing_first_name']['class'] = array( 'form-row-wide' );

        unset( $fields['billing']['billing_country'] );

        return $fields;
    }

    /**
     * Add needed scripts and styles
     */
    public function enqueue_scripts()
    {
        if( is_checkout() ) {
            wp_enqueue_style( 'poc_merchant_checkout', POC_MERCHANT_PLUGIN_URL . 'assets/checkout.css' );

            wp_enqueue_script( 'poc_merchant_checkout', POC_MERCHANT_PLUGIN_URL . 'assets/checkout.js', array( 'jquery', 'wc-checkout' ), '1.0.0' );

            wp_localize_script( 'poc_merchant_checkout', 'poc_merchant_checkout_data',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' )
                )
            );
        }

        wp_enqueue_style( 'poc_merchant_ajax_search', POC_MERCHANT_PLUGIN_URL . 'assets/ajax-search.css' );

        wp_enqueue_script( 'poc_merchant_ajax_search', POC_MERCHANT_PLUGIN_URL . 'assets/ajax-search.js', array( 'jquery' ) );

        wp_localize_script( 'poc_merchant_ajax_search', 'poc_merchant_ajax_search_data',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );
    }

    /**
     * Localisatize address formats
     *
     * @param array $arg
     *
     * @return array
     */
    public function localisation_address_formats( $arg )
    {
        unset($arg['default']);
        unset($arg['VN']);

        $arg['default'] = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
        $arg['VN'] = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";

        return $arg;
    }

    /**
     * Format billing address
     *
     * @param array $billing_address
     * @param \WC_Order $order_object
     *
     * @return array
     */
    public function order_formatted_billing_address( $billing_address, $order_object )
    {
        if( ! $billing_address ) {
            return array();
        }

        $oder_id = $order_object->get_id();

        $nameTinh = $this->get_name_city( get_post_meta( $oder_id, '_billing_state', true ) );
        $nameQuan = $this->get_name_district( get_post_meta( $oder_id, '_billing_city', true ) );
        $nameXa = $this->get_name_village( get_post_meta( $oder_id, '_billing_address_2', true ) );

        unset( $billing_address['state'] );
        unset( $billing_address['city'] );
        unset( $billing_address['address_2'] );

        $billing_address['state'] = $nameTinh;
        $billing_address['city'] = $nameQuan;
        $billing_address['address_2'] = $nameXa;

        return $billing_address;
    }

    public function set_default_checkout_country()
    {
        return 'VN';
    }

    public function get_order_address( $value, $type )
    {
        if($type == 'billing' || $type == 'shipping'){
            if(isset($value['state']) && $value['state']){
                $state = $value['state'];
                $value['state'] = $this->get_name_city($state);
            }
            if(isset($value['city']) && $value['city']){
                $city = $value['city'];
                $value['city'] = $this->get_name_district($city);
            }
            if(isset($value['address_2']) && $value['address_2']){
                $address_2 = $value['address_2'];
                $value['address_2'] = $this->get_name_village($address_2);
            }
        }

        return $value;
    }

    /**
     * Set default variation product for add-to-cart direct link
     *
     * @param int $product_id
     *
     * @return int
     */
    public function set_default_variation_id( $product_id )
    {
        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            return $product_id;
        }

        $product_type = $product->get_type();

        if ( $product_type === 'simple' || $product_type === 'variation' ) {
            return $product_id;
        }

        return $product->get_children()[0];
    }

    public function modify_cart_item_quantity( $result, $cart_item, $cart_item_key )
    {
        ob_start(); ?>
            <div id="poc-merchant-checkout-modify">
                <a href="" class="poc-merchant-checkout-edit">Edit</a>
                <div id="poc-merchant-checkout-modify-form" style="display: none;">
                    <label for="">Quantity</label>
                    <input type="number" name="cart[<?php echo $cart_item_key; ?>][quantity]" value="<?php echo $cart_item['quantity']; ?>">
                    <?php
                        $product = wc_get_product( $cart_item['product_id'] );

                        $variation = wc_get_product( $cart_item['variation_id'] );

                        if( $variation ) {
                            $variation_attributes = $variation->get_attributes();
                        }

                        $attributes = $product->get_attributes();

                        foreach ( $attributes as $attribute ) :
                    ?>
                            <label for="<?php echo $attribute->get_taxonomy(); ?>"><?php echo wc_attribute_label( $attribute->get_taxonomy() ); ?></label>
                            <select name="cart[<?php echo $cart_item_key; ?>][attributes][<?php echo $attribute->get_taxonomy(); ?>]" id="">
                                <?php foreach ( $attribute->get_terms() as $term ) : ?>
                                    <option <?php echo ( $variation && $variation_attributes[$attribute->get_taxonomy()] === $term->slug ) ? 'selected' : ''; ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                    <?php
                        endforeach;
                    ?>
                    <a href="" class="poc-merchant-checkout-cancel">Cancel</a>
                </div>
            </div>
        <?php
        $html = ob_get_clean();
        return $result .= $html;
    }

    public function add_order_meta_data( $order_id, $order )
    {
        $state = get_post_meta( $order_id, '_billing_state', true );

        $city = get_post_meta( $order_id, '_billing_city', true );

        $address_1 = get_post_meta( $order_id, '_billing_address_1', true );

        $address_2 = get_post_meta( $order_id, '_billing_address_2', true );

        $full_address = array(
            $address_1,
            $this->get_name_village($address_2),
            $this->get_name_district($city),
            $this->get_name_city($state)
        );

        update_post_meta( $order_id, 'full_address', implode( ', ', $full_address ) );
    }

    protected function get_name_city( $id = '' )
    {
        include POC_MERCHANT_PLUGIN_DIR . '/assets/data/tinh_thanhpho.php';

        if( is_numeric( $id ) ) {
            $id_tinh = sprintf( "%02d", intval( $id ) );
            if( ! is_array( $tinh_thanhpho ) || empty( $tinh_thanhpho ) ) {
                include POC_MERCHANT_PLUGIN_DIR . '/assets/data/tinh_thanhpho_old.php';
            }
        } else {
            $id_tinh = wc_clean( wp_unslash( $id ) );
        }

        return ( isset( $tinh_thanhpho[$id_tinh] ) ) ? $tinh_thanhpho[$id_tinh] : '';
    }

    protected function get_name_district($id = ''){
        include POC_MERCHANT_PLUGIN_DIR . '/assets/data/quan_huyen.php';

        $id_quan = sprintf( "%03d", intval( $id ) );

        if( is_array( $quan_huyen ) && ! empty( $quan_huyen ) ) {
            $nameQuan = Helpers::search_in_array( $quan_huyen, 'maqh', $id_quan );
            return isset( $nameQuan[0]['name'] ) ? $nameQuan[0]['name'] : '';
        }

        return false;
    }

    protected function get_name_village($id = ''){
        include POC_MERCHANT_PLUGIN_DIR . '/assets/data/xa_phuong_thitran.php';

        $id_xa = intval( $id );

        if( is_array( $xa_phuong_thitran ) && ! empty( $xa_phuong_thitran ) ) {
            $name = Helpers::search_in_array( $xa_phuong_thitran, 'xaid', $id_xa );
            return isset( $name[0]['name'] ) ? $name[0]['name'] : '';
        }

        return false;
    }

    /**
     * On activate plugin
     */
    public static function activate()
    {

    }

    /**
     * On deactivate plugin
     */
    public static function deactivate()
    {

    }
}
