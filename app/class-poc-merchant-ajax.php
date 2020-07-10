<?php

namespace App;

use App\Utilities\Helpers;

class POC_Merchant_AJAX
{
	/**
	 * Handle load address AJAX request
	 */
    public function load_address()
    {
        $matp = isset( $_POST['matp'] ) ? wc_clean( wp_unslash( $_POST['matp'] ) ) : '';

        $maqh = isset( $_POST['maqh'] ) ? intval( $_POST['maqh'] ) : '';

        if( $matp ){
            $result = $this->get_list_district( $matp );
            wp_send_json_success( $result );
        }

        if( $maqh ){
            $result = $this->get_list_village( $maqh );
            wp_send_json_success( $result );
        }

        wp_send_json_error();

        die();
    }

	/**
	 * Handle update cart on checkout page AJAX request
	 *
	 * @throws \Exception
	 */
    public function update_cart_on_checkout()
    {
        $form_data = array();
        parse_str( $_POST['form_data'], $form_data );
        $cart = $form_data['cart'];

        foreach ( $cart as $cart_key => $cart_data ) {
            $attributes = $cart_data['attributes'];

            $match_attributes = array();

            foreach ( $attributes as $attribute_name => $attribute_value ) {
                $match_attributes[wc_variation_attribute_name( $attribute_name )] = $attribute_value;
            }

            $cart_item = WC()->cart->get_cart_item( $cart_key );

            $data_store = \WC_Data_Store::load( 'product' );

            $variation_id = $data_store->find_matching_product_variation(
                new \WC_Product( $cart_item['product_id'] ),
                $match_attributes
            );

            WC()->cart->remove_cart_item( $cart_key );
            WC()->cart->add_to_cart( $cart_item['product_id'], $form_data['cart'][$cart_key]['quantity'], $variation_id, $match_attributes );
        }

        wp_send_json_success();
    }

	/**
	 * Handle product search AJAX request
	 */
    public function product_search()
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            's' => $_POST['s'] ? $_POST['s'] : ''
        );

        $search = new \WP_Query( $args );

        $products = array();

        if ( $search->have_posts() ) {
            while ( $search->have_posts() ) {
                $search->the_post();

                $products[] = array(
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'thumbnail' => get_the_post_thumbnail_url(),
                    'price' => wc_get_product( get_the_ID() )->get_price_html(),
                );
            }
        }

        wp_send_json_success( $products );
    }

	/**
	 * Get list district by city
	 *
	 * @param string $matp
	 *
	 * @return array|bool
	 */
    protected function get_list_district($matp = ''){
        if( ! $matp ) {
            return false;
        }

        if( is_numeric( $matp ) ) {
            require_once POC_MERCHANT_PLUGIN_DIR . '/assets/data/quan_huyen_old.php';
            $matp = sprintf( "%02d", intval( $matp ) );
        } else {
            require_once POC_MERCHANT_PLUGIN_DIR . '/assets/data/quan_huyen.php';
            $matp = wc_clean( wp_unslash( $matp ) );
        }

        return Helpers::search_in_array( $quan_huyen, 'matp', $matp );
    }

	/**
	 * Get list village by district
	 *
	 * @param string $maqh
	 *
	 * @return array|bool
	 */
    protected function get_list_village($maqh = ''){
        if( ! $maqh ) {
            return false;
        }

        require_once POC_MERCHANT_PLUGIN_DIR . '/assets/data/xa_phuong_thitran.php';

        $id_xa = intval( $maqh );

        return Helpers::search_in_array( $xa_phuong_thitran, 'maqh', $id_xa );
    }
}