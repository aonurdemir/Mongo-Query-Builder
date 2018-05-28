<?php defined('SYSPATH') or die('No direct access allowed');
/**
 * Mongo Query Builder.
 *
 * Usage Example:
 *
 * $builder = new Helper_Mongoquerybuilder();
 * $builder->add('loc')->add('$near')->add('$geometry')->add('type','Point');
 * $builder->add('loc')->add('$near')->add('$geometry')->add('coordinates',[$current_pos[0],$current_pos[1]]);
 * $builder->add('loc')->add('$near')->add('$maxDistance',$max_dist*1000); //in meters
 * $builder->add('loc')->add('$near')->add('$minDistance',$min_dist*1000); //in meters
 * $builder->add('gender',true);
 * $builder->add('suspended',false);
 * $query = $builder->get_query();
 *
 * @package    Helper
 * @author     Onur Demir
 */
class Helper_Mongoquerybuilder
{
    private $base;
    public function __construct()
    {
        $this->base = array();
    }
    /**
     * Add a new key to the query with its value.
     *
     * @param string $key MongoDB document key.
     * @param mixed $value MongoDB document key's value.
     * @return Helper_Mongoquerybuilder|null Returns a Helper_Mongoquerybuilder instance reprensting the key.
     */
    public function &add($key, $value = null)
    {
        //If key exists in the query, return it.
        if (array_key_exists($key, $this->base)) {
            $query_key = $this->base[$key];
            return $query_key;
        }
        //If the key's value is null, meaning that it will include more keys in it,
        //create a new instance of this class and return its reference.
        if (is_null($value)) {
            $query_key = new Helper_Mongoquerybuilder();
            $this->base[$key] = $query_key;
            return $query_key;
        } else {//If the key is primitive, just add it to the base. Returning value is a dummy operation.
            $this->base[$key] = $value;
            return $value;
        }
    }
    /**
     * @return array Return the base array.
     */
    private function get_base()
    {
        return $this->base;
    }
    /**
     * Get the structured query as MongoDB query array.
     *
     * @return array Returns an array structed for MongoDB query.
     */
    public function get_query($object = null)
    {
        $array = array();
        if (is_null($object)) {
            $object = $this->base;
        }
        //traverse each key in the list
        foreach ($object as $key => $value) {
            //if key consists of more nested keys,
            if ($value instanceof Helper_Mongoquerybuilder) {
                //get all nested keys as array, recursively
                $array[$key] = $this->get_query($value->get_base());
            } else {//if not, get the key directly
                $array[$key] = $value;
            }
        }
        return $array;
    }
}
