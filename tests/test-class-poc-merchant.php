<?php

namespace Tests;

use App\POC_Merchant;
use Tests\Helpers\WC_Helper_Product;

class Test_Class_POC_Merchant extends \WP_UnitTestCase
{
    public $instance;

    public function setUp()
    {
        parent::setUp();

        $this->instance = POC_Merchant::instance();
    }

    public function test_add_hooks()
    {
        $this->assertGreaterThan(
            0,
            has_filter(
                'woocommerce_checkout_fields',
                array( $this->instance, 'modify_checkout_fields' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_ajax_poc_merchant_load_address',
                array( $this->instance->ajax, 'load_address' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_ajax_nopriv_poc_merchant_load_address',
                array( $this->instance->ajax, 'load_address' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_enqueue_scripts',
                array( $this->instance, 'enqueue_scripts' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_filter(
                'woocommerce_localisation_address_formats',
                array( $this->instance, 'localisation_address_formats' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_filter(
                'woocommerce_order_formatted_billing_address',
                    array( $this->instance, 'order_formatted_billing_address' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_filter(
                'default_checkout_billing_country',
                array( $this->instance, 'set_default_checkout_country' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_filter(
                'woocommerce_get_order_address',
                array( $this->instance, 'get_order_address' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_filter(
                'woocommerce_add_to_cart_product_id',
                array( $this->instance, 'set_default_variation_id' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_filter(
                'woocommerce_checkout_cart_item_quantity',
                array( $this->instance, 'modify_cart_item_quantity' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_ajax_poc_merchant_update_cart_on_checkout',
                array( $this->instance->ajax, 'update_cart_on_checkout' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_ajax_nopriv_poc_merchant_update_cart_on_checkout',
                array( $this->instance->ajax, 'update_cart_on_checkout' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_ajax_poc_merchant_product_search',
                array( $this->instance->ajax, 'product_search' )
            )
        );

        $this->assertGreaterThan(
            0,
            has_action(
                'wp_ajax_nopriv_poc_merchant_product_search',
                array( $this->instance->ajax, 'product_search' )
            )
        );
    }

    public function test_modify_checkout_fields()
    {
        $fields = $this->instance->modify_checkout_fields( array() );

        $this->assertSame( 'Province/City', $fields['billing']['billing_state']['label'] );
        $this->assertTrue( $fields['billing']['billing_state']['required'] );
        $this->assertSame( 'select', $fields['billing']['billing_state']['type'] );

        $this->assertSame( 'District', $fields['billing']['billing_city']['label'] );
        $this->assertTrue( $fields['billing']['billing_city']['required'] );
        $this->assertSame( 'select', $fields['billing']['billing_city']['type'] );

        $this->assertSame( 'Commune/Ward/Town', $fields['billing']['billing_address_2']['label'] );
        $this->assertTrue( $fields['billing']['billing_address_2']['required'] );
        $this->assertSame( 'select', $fields['billing']['billing_address_2']['type'] );

        $this->assertSame( array( 'form-row-last' ), $fields['billing']['billing_address_1']['class'] );
        $this->assertSame( 60, $fields['billing']['billing_address_1']['priority'] );

        $this->assertSame( array( 'form-row-wide' ), $fields['billing']['billing_first_name']['class'] );

        $this->assertTrue( ! isset( $fields['billing']['billing_country'] ) );
    }

    public function test_localisation_address_formats()
    {
        $arg = $this->instance->localisation_address_formats( array(
            'default' => '',
            'VN' => '',
        ) );

        $this->assertSame( "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}", $arg['default'] );
        $this->assertSame( "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}", $arg['VN'] );
    }

    public function test_order_formatted_billing_address()
    {
        $order = wc_create_order();

        update_post_meta( $order->get_id(), '_billing_state', 'HANOI' );

        update_post_meta( $order->get_id(), '_billing_city', '1' );

        update_post_meta( $order->get_id(), '_billing_address_2', '1' );

        $result = $this->instance->order_formatted_billing_address( $order->get_address( 'billing' ), $order );

        $this->assertSame( 'Hà Nội', $result['state'] );
        $this->assertSame( 'Quận Hoàn Kiếm', $result['city'] );
        $this->assertSame( 'Phường Hàng Buồm', $result['address_2'] );
    }

    public function test_set_default_checkout_country()
    {
        $this->assertSame( 'VN', $this->instance->set_default_checkout_country() );
    }

    public function test_get_order_address()
    {
        $value = array(
            'state' => 'HANOI',
            'city' => '1',
            'address_2' => '1',
        );

        $result = $this->instance->get_order_address( $value, 'billing' );

        $this->assertSame( 'Hà Nội', $result['state'] );
        $this->assertSame( 'Quận Hoàn Kiếm', $result['city'] );
        $this->assertSame( 'Phường Hàng Buồm', $result['address_2'] );
    }

    public function test_set_default_variation_id()
    {
        $simple_product = WC_Helper_Product::create_simple_product();

        $this->assertSame( $simple_product->get_id(), $this->instance->set_default_variation_id( $simple_product->get_id() ) );

        $variation_product = WC_Helper_Product::create_variation_product();

        $this->assertSame( $variation_product->get_children()[0], $this->instance->set_default_variation_id( $variation_product->get_id() ) );
    }
}