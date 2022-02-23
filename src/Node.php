<?php

namespace fengxue145\pdf;

class Node implements NodeInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $attrs;

    /**
     * @var array
     */
    protected $children;

    public function __construct($type, array $attrs = array(), $children = null)
    {
        $this->id = uniqid('NODE', true);
        $this->type = $type;
        $this->attrs = $attrs;
        $this->children = [];
        if ($children !== null) {
            $this->spliceChild(0, 0, is_array($children) ? $children : [$children]);
        }
    }

    /**
     * @param string $name
     * 
     * @return mixed|void
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * @param string $key
     * 
     * @return mixed|void
     */
    public function getAttr($key)
    {
        if (isset($this->attrs[$key])) {
            return $this->attrs[$key];
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttr($key, $value)
    {
        $this->attrs[$key] = $value;
    }

    /**
     * @param string $key
     * 
     * @return mixed|void
     */
    public function removeProps($key)
    {
        unset($this->props[$key]);
    }

    /**
     * @param string|NodeInterface $newchild
     * 
     * @return bool
     */
    public function appendChild($newchild)
    {
        if (is_node($newchild)) {
            $this->children[] = $newchild;
            return true;
        }
        return false;
    }

    /**
     * @see array_splice()
     */
    public function spliceChild($offset, $length = null, $childs = array())
    {
        if ($length === null) {
            $length = count($this->children);
        }
        $childs = array_filter($childs, function ($v) {
            return is_node($v);
        });
        return array_splice($this->children, $offset, $length, $childs);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $html = sprintf('<%s%s>', $this->type, attr2str($this->attrs));
        foreach ($this->children as $val) {
            $html .= (string)$val;
        }
        $html .= sprintf('</%s>', $this->type);
        return $html;
    }
}
