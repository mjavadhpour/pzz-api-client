<?php
/**
 * Array Helper class
 *
 * @since 1.2.0
 * @package WordPress
 */
class PZZ_Array_Helper
{
    /**
     * Get all of the given elements from an array.
     *
     * @since    1.2.0
     * @param    Array   $data          The array of data that we want to retrive given elements from.
     * @param    Array   $elements      Elements names.
     * @return   Array
    */
    public static function get_elements($data, $elements)
    {
        return array_intersect_key($data, array_flip($elements));
    }
}
