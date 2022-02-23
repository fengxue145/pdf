<?php

namespace fengxue145\pdf;

/**
 * @param string $type
 * @param array $attrs
 * @param mixed $children
 * @return Node
 */
function h($type, array $attrs = array(), $children = null)
{
    return new Node($type, $attrs, $children);
}

/**
 * @param mixed $val
 * @return bool
 */
function is_node($val)
{
    return ($val instanceof NodeInterface) || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'));
}

/**
 * @param Node $node
 * @param array $array
 * @return Node
 */
function array2node(Node $node, array $array)
{
    foreach ($array as $item) {
        if (!isset($item['type'])) continue;

        $item += ['attrs' => []];
        $new_node = h($item['type'], $item['attrs']);

        if (isset($item['children'])) {
            if (is_array($item['children'])) {
                array2node($new_node, $item['children']);
            } else {
                $new_node->appendChild($item['children']);
            }
        }
        $node->appendChild($new_node);
    }
    return $node;
}

/**
 * @return string
 */
function style2str(array $styles, callable $callback = null)
{
    $str = '';
    $is_callable = is_callable($callback);

    foreach ($styles as $k => $v) {
        if ($is_callable) {
            list($k, $v) = call_user_func($callback, $k, $v);
        }
        $str .= sprintf('%s:%s;', $k, (string)$v);
    }
    return $str;
}

/**
 * @return string
 */
function attr2str(array $attrs, callable $callback = null)
{
    $str = '';
    $is_callable = is_callable($callback);

    foreach ($attrs as $k => $v) {
        if ($is_callable) {
            list($k, $v) = call_user_func($callback, $k, $v);
        } else {
            if (is_array($v)) {
                if ($k === 'style') {
                    $v = style2str($v);
                } else {
                    $v = implode(' ', $v);
                }
                $str .= sprintf(' %s="%s"', $k, $v);
            } else if (is_bool($v)) {
                $str .= sprintf(' %s', $k);
            } else {
                $str .= sprintf(' %s="%s"', $k, (string)$v);
            }
        }
    }
    return $str;
}

/**
 * @param object $object
 * @param string $name
 * @return mixed
 */
function &get_object_property($object, $name)
{
    static $index = [];

    if (!is_object($object)) {
        throw new \TypeError(sprintf('Argument 1 passed to %s() must be of the type object, %s given', __FUNCTION__, gettype($object)));
    }

    $key = sprintf('%s::%s', get_class($object), $name);
    if (!isset($index[$key])) {
        $reflect = new \ReflectionObject($object);
        $reflectProperty = $reflect->getProperty($name);
        if (!$reflectProperty->isPublic()) {
            $reflectProperty->setAccessible(true);
        }
        $index[$key] = $reflectProperty->getValue($object);
    }
    return $index[$key];
}
