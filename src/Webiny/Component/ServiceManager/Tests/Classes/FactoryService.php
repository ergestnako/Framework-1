<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests\Classes;


class FactoryService
{

    public static function getObject($parameter)
    {
        return $parameter;
    }
}