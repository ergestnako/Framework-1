<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler;

use Webiny\Component\Rest\Parser\ParsedApi;
use Webiny\Component\Rest\Parser\ParsedClass;
use Webiny\Component\Rest\Parser\PathTransformations;

/**
 * Compiler transforms ParsedClass instances into a special array.
 * This array is then written on the disk in the compile path.
 *
 * @package         Webiny\Component\Rest\Compiler
 */
class Compiler
{
    /**
     * @var string Name of the api configuration.
     */
    private $api;

    /**
     * @var bool Should the class name and the method name be normalized.
     */
    private $normalize;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Base constructor.
     *
     * @param string $api       Name of the api configuration.
     * @param bool   $normalize Should the class name and the method name be normalized.
     * @param Cache  $cache     Current compiler cache instance.
     */
    public function __construct($api, $normalize, Cache $cache)
    {
        $this->api = $api;
        $this->normalize = $normalize;
        $this->cache = $cache;
    }

    /**
     * Based on the given ParsedApi instance, the method will create several cache file and update the
     * cache index.
     *
     * @param ParsedApi $parsedApi
     */
    public function writeCacheFiles(ParsedApi $parsedApi)
    {
        $writtenCacheFiles = [];

        // first delete the cache
        foreach ($parsedApi->versions as $v => $parsedClass) {
            $this->cache->deleteCache($this->api, $parsedApi->apiClass);
        }

        // then build the cache
        foreach ($parsedApi->versions as $v => $parsedClass) {
            $compileArray = $this->compileCacheFile($parsedClass, $v);

            $this->cache->writeCacheFile($this->api, $parsedApi->apiClass, $v, $compileArray);

            $writtenCacheFiles[$v] = $compileArray;
        }

        // write current and latest versions (just include return a specific version)
        $this->cache->writeCacheFile($this->api, $parsedApi->apiClass, 'latest',
            $writtenCacheFiles[$parsedApi->latestVersion]);
        $this->cache->writeCacheFile($this->api, $parsedApi->apiClass, 'current',
            $writtenCacheFiles[$parsedApi->currentVersion]);
    }

    /**
     * This method does the actual processing of ParsedClass instance into a compiled array that is later
     * written into a cache file.
     *
     * @param ParsedClass $parsedClass ParsedClass instance that will be compiled into an array.
     * @param string      $version     Version of the API.
     *
     * @return array The compiled array.
     */
    private function compileCacheFile(ParsedClass $parsedClass, $version)
    {
        $compileArray = [];
        $compileArray['class'] = $parsedClass->class;
        $compileArray['cacheKeyInterface'] = $parsedClass->cacheKeyInterface;
        $compileArray['accessInterface'] = $parsedClass->accessInterface;
        $compileArray['version'] = $version;


        foreach ($parsedClass->parsedMethods as $m) {
            $url = $this->buildUrlMatchPattern($m->name, $m->params);
            $compileArray[$m->method][$url] = [
                'default'     => $m->default,
                'role'        => ($m->role) ? $m->role : false,
                'method'      => $m->name,
                'urlPattern'  => $m->urlPattern,
                'cache'       => $m->cache,
                'header'      => $m->header,
                'rateControl' => $m->rateControl,
                'params'      => []
            ];

            foreach ($m->params as $p) {
                $compileArray[$m->method][$url]['params'][$p->name] = [
                    'required' => $p->required,
                    'type'     => $p->type,
                    'default'  => $p->default
                ];
            }
        }

        return $compileArray;
    }

    /**
     * Builds the url match pattern for each of the method inside the api.
     *
     * @param string $methodName Method name.
     * @param array  $parameters List of the ParsedParameter instances.
     *
     * @return string The url pattern.
     */
    private function buildUrlMatchPattern($methodName, array $parameters)
    {
        $url = $methodName;
        if ($this->normalize) {
            $url = PathTransformations::methodNameToUrl($methodName);
        }

        foreach ($parameters as $p) {
            $matchType = $this->getParamMatchType($p->type);
            $url = $url . '/' . $matchType;
        }

        return $url . '/';
    }

    /**
     * Returns a different match pattern, based on the given $paramType.
     *
     * @param string $paramType Parameter type name.
     *
     * @return string Match pattern.
     */
    private function getParamMatchType($paramType)
    {
        switch ($paramType) {
            case 'string':
                return '([\w-]+)';
                break;
            case 'bool':
                return '(0|1|true|false)';
                break;
            case 'integer':
                return '([\d]+)';
                break;
            case 'float':
                return '([\d.]+)';
                break;
            default:
                return '([\w-]+)';
                break;
        }
    }

}