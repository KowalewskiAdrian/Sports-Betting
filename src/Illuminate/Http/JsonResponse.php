<?php

namespace Illuminate\Http;

use JsonSerializable;
use InvalidArgumentException;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    use ResponseTrait, Macroable {
        Macroable::__call as macroCall;
    }

    /**
     * Constructor.
     *
     * @param  mixed  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return void
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        $this->encodingOptions = $options;

        parent::__construct($data, $status, $headers);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param  string|null  $callback
     * @return $this
     */
    public function withCallback($callback = null)
    {
        return $this->setCallback($callback);
    }

    /**
     * Get the json_decoded data from the response.
     *
     * @param  bool  $assoc
     * @param  int  $depth
     * @return mixed
     */
    public function getData($assoc = false, $depth = 512)
    {
        return json_decode($this->data, $assoc, $depth);
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        $this->original = $data;

        if ($data instanceof Jsonable) {
            $this->data = $data->toJson($this->encodingOptions);
        } elseif ($data instanceof JsonSerializable) {
            $this->data = json_encode($data->jsonSerialize(), $this->encodingOptions);
        } elseif ($data instanceof Arrayable) {
            $this->data = json_encode($data->toArray(), $this->encodingOptions);
        } else {
            $this->data = json_encode($data, $this->encodingOptions);
        }

        if (! $this->hasValidJson(json_last_error())) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $this->update();
    }

    /**
     * Determine if an error occurred during JSON encoding.
     *
     * @param  int  $jsonError
     * @return bool
     */
    protected function hasValidJson($jsonError)
    {
        // No error is obviously fine
        if ($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        // If the JSON_PARTIAL_OUTPUT_ON_ERROR option is set, some additional errors are fine
        // (see https://secure.php.net/manual/en/json.constants.php)
        if ($this->hasEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR)) {
            $acceptableErrors = [
                JSON_ERROR_RECURSION,
                JSON_ERROR_INF_OR_NAN,
                JSON_ERROR_UNSUPPORTED_TYPE,
            ];

            return \in_array($jsonError, $acceptableErrors);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodingOptions($options)
    {
        $this->encodingOptions = (int) $options;

        return $this->setData($this->getData());
    }

    /**
     * Determine if a JSON encoding option is set.
     *
     * @param  int  $option
     * @return bool
     */
    public function hasEncodingOption($option)
    {
        return (bool) ($this->encodingOptions & $option);
    }
}
