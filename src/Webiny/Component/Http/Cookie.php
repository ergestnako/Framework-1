<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Cookie\CookieException;
use Webiny\Component\Http\Cookie\CookieStorageInterface;
use Webiny\Component\Http\Cookie\Storage\NativeStorage;
use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Cookie Http component.
 *
 * @package         Webiny\Component\Http
 */
class Cookie
{
    use StdLibTrait, FactoryLoaderTrait, SingletonTrait;

    private $cookieBag;
    private $storage;
    private $cookiePrefix = '';
    private $defaultTtl = 86400;


    /**
     * Constructor.
     *
     * @throws \Webiny\Component\Http\Cookie\CookieException
     */
    protected function init()
    {
        try {
            // get config
            $config = self::getConfig();


            // create storage
            $this->getStorage($config);

            // get all cookies from the driver
            $cookies = $this->getStorage()->getAll();
            $this->cookieBag = $this->arr($cookies);

            // set cookie prefix
            $this->cookiePrefix = $config->get('Prefix', '');

            // set default ttl
            $this->defaultTtl = $config->get('ExpireTime', 86400);
        } catch (\Exception $e) {
            throw new CookieException($e->getMessage());
        }
    }

    /**
     * Save a cookie.
     *
     * @param string $name Name of the cookie.
     * @param string $value Cookie value.
     * @param int    $expiration Timestamp when the cookie should expire.
     * @param bool   $httpOnly Is the cookie https-only or not.
     * @param string $path Path under which the cookie is accessible.
     *
     * @return bool True if cookie was save successfully, otherwise false.
     * @throws CookieException
     */
    public function save($name, $value, $expiration = null, $httpOnly = true, $path = '/')
    {

        // prepare params
        $name = $this->cookiePrefix . $name;
        $expiration = (is_null($expiration)) ? $this->defaultTtl : $expiration;
        $expiration += time();

        try {
            $result = $this->getStorage()->save($name, $value, $expiration, $httpOnly, $path);
            if ($result) {
                $this->cookieBag->removeKey($name)->append($name, $value);
            }
        } catch (\Exception $e) {
            throw new CookieException($e->getMessage());
        }

        return $result;
    }

    /**
     * Get the cookie.
     *
     * @param string $name Cookie name.
     *
     * @return string|bool String if cookie is found, false if cookie is not found.
     */
    public function get($name)
    {
        return $this->cookieBag->key($this->cookiePrefix . $name, false, true);
    }

    /**
     * Remove the given cookie.
     *
     * @param string $name Cookie name.
     *
     * @return bool True if cookie was deleted, or if it doesn't exist, otherwise false.
     * @throws \Webiny\Component\Http\Cookie\CookieException
     */
    public function delete($name)
    {
        try {
            $result = $this->getStorage()->delete($this->cookiePrefix . $name);
            $this->cookieBag->removeKey($this->cookiePrefix . $name);
        } catch (\Exception $e) {
            throw new CookieException($e->getMessage());
        }

        return $result;
    }

    /**
     * Get cookie storage driver.
     *
     * @param ConfigObject|null $config Cookie config - needed only if storage driver does not yet exist.
     *
     * @return CookieStorageInterface
     * @throws \Webiny\Component\Http\Cookie\CookieException
     */
    private function getStorage(ConfigObject $config = null)
    {
        if (!isset($this->storage)) {
            try {
                $driver = $config->get('Storage.Driver', NativeStorage::class);
                $this->storage = $this->factory($driver, CookieStorageInterface::class, [$config]);
            } catch (Exception $e) {
                throw new CookieException($e->getMessage());
            }
        }

        return $this->storage;
    }

    /**
     * Returns cookie config from Http object.
     *
     * @return ConfigObject
     */
    public static function getConfig()
    {
        return Http::getConfig()->get('Cookie', new ConfigObject([]));
    }
}