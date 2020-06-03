<?php

namespace App\Utilities;

class Helpers
{
    public static function search_in_array($array, $key, $value)
    {
        if( ! is_array( $array ) ) {
            return array();
        }

        $results = array();

        if ( isset( $array[$key] ) && $array[$key] == $value ) {
            $results[] = $array;
        } elseif( isset( $array[$key] ) && is_serialized( $array[$key] ) && in_array( $value, maybe_unserialize( $array[$key] ) ) ) {
            $results[] = $array;
        }

        foreach ( $array as $subarray ) {
            $results = array_merge( $results, self::search_in_array( $subarray, $key, $value ) );
        }

        return $results;
    }
}