<?php
namespace Opensoft\SimpleSerializer\Metadata\Annotations;

/**
 * @author Evgeny Sugatov
 * @Annotation
 * @Target("PROPERTY")
 */
final class Groups
{
    /**
     * @Required
     * @var array
     */
    public $value;
}
