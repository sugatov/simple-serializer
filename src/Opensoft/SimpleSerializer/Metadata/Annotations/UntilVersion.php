<?php
namespace Opensoft\SimpleSerializer\Metadata\Annotations;

/**
 * @author Evgeny Sugatov
 * @Annotation
 * @Target("PROPERTY")
 */
final class UntilVersion
{
    /**
     * @Required
     * @var string
     */
    public $value;
}
