<?php

namespace Illuminate\Database\Eloquent;

use RuntimeException;

class JsonEncodingException extends RuntimeException
{
    /**
     * Create a new JSON encoding exception for the model.
     *
     * @param  mixed  $model
     * @param  string  $message
     * @return static
     */
    public static function forModel($model, $message)
    {
        return new static('Error encoding model ['.get_class($model).'] with ID ['.$model->getKey().'] to JSON: '.$message);
    }

    /**
     * Create a new JSON encoding exception for an attribute.
     *
     * @param  mixed  $key
     * @param  string $message
     * @return static
     * @internal param mixed $key
     */
    public static function forAttribute($key, $message)
    {
        return new static('Error encoding value of attribute ['.$key.'] to JSON: '.$message);
    }
}
