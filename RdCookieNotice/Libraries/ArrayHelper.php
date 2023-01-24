<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package rd-cookie-notice
 */


namespace RdCookieNotice\Libraries;


/**
 * Array helper class.
 * 
 * @since 0.2.0
 */
class ArrayHelper
{


    /**
     * Merge multidimensional associative arrays.
     * Works only with strings, integers and arrays as keys. Values can be any type but they have to have same type to be kept in the final array.
     * Every array should have the same type of elements. Only keys from $defaults array will be kept in the final array unless $siblings are not empty.
     * $siblings examples: array('=>', 'only_first_level', 'first_level=>second_level', 'first_key=>next_key=>sibling') and so on.
     * Single '=>' means that all siblings of the highest level will be kept in the final array.
     *
     * @param array $defaults Array with defaults values
     * @param array $arrayVal Array to merge
     * @param boolean|array $siblings Whether to allow "string" siblings to copy from $array if they do not exist in $defaults, false otherwise
     * @return array Merged arrays
     */
    public function multiArrayMerge($defaults, $arrayVal, $siblings = false)
    {
        // make a copy for better performance and to prevent $default override in foreach
        $copy = $defaults;

        // prepare siblings for recursive deeper level
        $new_siblings = [];

        if (!empty($siblings) && is_array($siblings)) {
            // if allow siblings
            foreach ($siblings as $sibling) {
                if ('=>' === $sibling) {
                    // if highest level siblings
                    // copy all non-existent string siblings
                    foreach ($arrayVal as $key => $value) {
                        if (is_string($key) && !array_key_exists($key, $defaults)) {
                            $defaults[$key] = null;
                        }
                    }
                } else {
                    // if sublevel siblings
                    // explode siblings
                    $ex = explode('=>', $sibling);

                    // copy all non-existent siblings
                    foreach (array_keys($arrayVal[$ex[0]]) as $key) {
                        if (!array_key_exists($key, $defaults[$ex[0]])) {
                            $defaults[$ex[0]][$key] = null;
                        }
                    }// endforeach;
                    unset($key);

                    if (count($ex) > 1) {
                        // if more than one sibling child
                        $new_siblings[$ex[0]] = [substr_replace($sibling, '', 0, strlen($ex[0] . '=>'))];
                    } else {
                        // if no more sibling children
                        $new_siblings[$ex[0]] = false;
                    }
                }
            }// endforeach;
            unset($sibling);
        }

        // loop through first array
        foreach ($defaults as $key => $value) {
            if (is_int($key)) {
                // if integer key
                $copy = array_unique(array_merge($defaults, $arrayVal), SORT_REGULAR);
                break;
            } elseif (is_string($key) && isset($arrayVal[$key])) {
                // if string key
                if (
                    (is_string($value) && is_string($arrayVal[$key])) || 
                    (is_bool($value) && is_bool($arrayVal[$key])) || 
                    (is_int($value) && is_int($arrayVal[$key])) || 
                    is_null($value)
                ) {
                    // if string, boolean, integer or null values
                    $copy[$key] = $arrayVal[$key];
                } elseif (is_array($value) && isset($arrayVal[$key]) && is_array($arrayVal[$key])) {
                    // if arrays
                    if (empty($value)) {
                        $copy[$key] = $arrayVal[$key];
                    } else {
                        $copy[$key] = $this->multiArrayMerge(
                            $defaults[$key], 
                            $arrayVal[$key], 
                            (isset($new_siblings[$key]) ? $new_siblings[$key] : false)
                        );
                    }
                }
            }
        }// endforeach;
        unset($key, $value);

        return $copy;
    }// multiArrayMerge


}
