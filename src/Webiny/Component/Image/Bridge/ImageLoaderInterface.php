<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Bridge;

use Webiny\Component\Storage\File\File;

/**
 * ImageLoader interface.
 * All image loaders must implement this interface.
 *
 * @package         Webiny\Component\Image\Bridge
 */
interface ImageLoaderInterface
{
    /**
     * Create a blank image with of given dimensions and fill it with $bgColor.
     *
     * @param int    $width       Width of the new image.
     * @param int    $height      Height of the new image.
     * @param string $bgColor     Background color. Following formats are acceptable
     *                            - "fff"
     *                            - "ffffff"
     *                            - array(255,255,255)
     *
     * @return ImageInterface
     */
    public function create($width, $height, $bgColor = null);

    /**
     * Creates a new ImageInterface instance from the given image at the provided path.
     *
     * @param File $image Path to an image on the disk.
     *
     * @return ImageInterface
     */
    public function open(File $image);

    /**
     * Create a new ImageInterface instance form the given binary string.
     *
     * @param string $string Binary string that holds image information.
     *
     * @return mixed
     */
    public function load($string);

    /**
     * Create a new ImageInterface instance from the given resource.
     *
     * @param mixed $resource Resource.
     *
     * @return ImageInterface
     */
    public function resource($resource);
}