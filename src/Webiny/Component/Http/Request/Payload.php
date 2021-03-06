<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Payload Http component.
 *
 * @package         Webiny\Component\Http
 */
class Payload
{
    use StdLibTrait;

    private $payloadBag;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $phpInput = file_get_contents('php://input');
        if(is_null($data) && $phpInput != ''){
            parse_str($phpInput, $data);
        }

        $this->payloadBag = $this->arr($data);
    }

    /**
     * Get the value from POST for the given $key.
     *
     * @param string $key   Key name.
     * @param null   $value Default value that will be returned if the $key is not found.
     *
     * @return string Value under the defined $key.
     */
    public function get($key, $value = null)
    {
        return $this->payloadBag->key($key, $value, true);
    }

    /**
     * Returns a list of all POST values.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->payloadBag->val();
    }
}