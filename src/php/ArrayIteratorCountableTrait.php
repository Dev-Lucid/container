<?php
/*
 * This file is part of the Lucid Container package.
 *
 * (c) Mike Thorn <mthorn@devlucid.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lucid\Container;

trait ArrayIteratorCountableTrait
{
    /* ArrayAccess methods: start */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    public function &offsetGet($id)
    {
        $value =& $this->get($id);
        return $value;
    }

    public function offsetSet($id, $newValue)
    {
        $this->set($id, $newValue);
    }

    public function offsetUnset($id)
    {
        $this->delete($id);
    }
    /* ArrayAccess methods: end */

    /* Iterator methods: start */
    function rewind() {
        reset($this->source);
    }

    function current() {
        return current($this->source);
    }

    function key() {
        return key($this->source);
    }

    function next() {
        next($this->source);
    }

    function valid() {
        return key($this->source) !== null;
    }
    /* Iterator methods: end */

    /* Countable methods: start */
    function count()
    {
        return count(array_keys($this->getValues()));
    }
    /* Countable methods: end */
}
