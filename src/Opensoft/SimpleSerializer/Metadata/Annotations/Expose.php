<?php
namespace Opensoft\SimpleSerializer\Metadata\Annotations;

/**
 * @author Evgeny Sugatov
 * @Annotation
 * @Target("PROPERTY")
 */
final class Expose
{
    /**
     * @Required
     * @var bool
     */
    public $value;
}
