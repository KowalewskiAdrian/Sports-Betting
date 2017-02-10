<?php

namespace Illuminate\Http;

use Illuminate\Support\Str;

trait FileHelpers
{
    /**
     * The file hash name.
     *
     * @var string
     */
    private $hashNameCache = null;

    /**
     * Get the fully qualified path to the file.
     *
     * @return string
     */
    public function path()
    {
        return $this->getRealPath();
    }

    /**
     * Get the file's extension.
     *
     * @return string
     */
    public function extension()
    {
        return $this->guessExtension();
    }

    /**
     * Get the file's extension supplied by the client.
     *
     * @return string
     */
    public function clientExtension()
    {
        return $this->guessClientExtension();
    }

    /**
     * Get a filename for the file.
     *
     * @param  string  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/').'/';
        }

        $hash = $this->hashNameCache ?: $this->hashNameCache = Str::random(40);

        return $path.$hash.'.'.$this->guessExtension();
    }
}
