<?php
namespace Opensoft\SimpleSerializer;

use Metadata\Cache\CacheInterface;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @author Evgeny Sugatov
 */
class Configuration
{
    const STRICT_MODE        = 2;
    const MEDIUM_STRICT_MODE = 1;
    const NON_STRICT_MODE    = 0;

    /**
     * @var boolean
     */
    private static $debugMode = false;

    /**
     * @var CacheInterface
     */
    private static $cacheDriver = null;

    /**
     * @var integer
     */
    private static $mode = self::NON_STRICT_MODE;

    /**
     * @param boolean $val
     */
    public static function setDebugMode($val)
    {
        self::$debugMode = $val;
    }

    /**
     * @param CacheInterface $driver
     */
    public static function setCacheDriver(CacheInterface $driver)
    {
        self::$cacheDriver = $driver;
    }

    /**
     * @param integer $val
     */
    public static function setMode($val)
    {
        if (in_array($val, array(self::STRICT_MODE, self::MEDIUM_STRICT_MODE, self::NON_STRICT_MODE))) {
            self::$mode = $val;
        }
    }

    public static function createAnnotationMetadataConfiguration()
    {
        return new Serializer(
            new Normalization\ArrayNormalizer(
                new Metadata\MetadataFactory(
                    new Metadata\Driver\AnnotationDriver(
                        new AnnotationReader()
                    ),
                    self::$cacheDriver,
                    self::$debugMode
                ),
                new Normalization\PropertySkipper(),
                new Normalization\ArrayNormalizer\DataProcessor(
                    new Normalization\ArrayNormalizer\TransformerFactory()
                ),
                self::$mode
            ),
            new Encoder\JsonEncoder()
        );
    }

    public static function createYamlMetadataConfiguration(array $paths)
    {
        return new Serializer(
            new Normalization\ArrayNormalizer(
                new Metadata\MetadataFactory(
                    new Metadata\Driver\YamlDriver(
                        new Metadata\Driver\FileLocator($paths)
                    ),
                    self::$cacheDriver,
                    self::$debugMode
                ),
                new Normalization\PropertySkipper(),
                new Normalization\ArrayNormalizer\DataProcessor(
                    new Normalization\ArrayNormalizer\TransformerFactory()
                ),
                self::$mode
            ),
            new Encoder\JsonEncoder()
        );
    }

}

