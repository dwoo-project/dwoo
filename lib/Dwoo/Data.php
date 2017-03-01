<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.4
 * @date      2017-03-01
 * @link      http://dwoo.org/
 */

namespace Dwoo;

/**
 * Dwoo data object, use it for complex data assignments or if you want to easily pass it
 * around multiple functions to avoid passing an array by reference.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class Data implements IDataProvider
{
    /**
     * Data array.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Returns the data array.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Clears a the entire data or only the given key.
     *
     * @param array|string $name clears only one value if you give a name, multiple values if
     *                           you give an array of names, or the entire data if left null
     */
    public function clear($name = null)
    {
        if ($name === null) {
            $this->data = array();
        } elseif (is_array($name)) {
            foreach ($name as $index) {
                unset($this->data[$index]);
            }
        } else {
            unset($this->data[$name]);
        }
    }

    /**
     * Overwrites the entire data with the given array.
     *
     * @param array $data the new data array to use
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * merges the given array(s) with the current data with array_merge.
     *
     * @param array $data  the array to merge
     */
    public function mergeData(array $data)
    {
        $args = func_get_args();
        foreach ($args as $key => $v) {
            if (is_array($v)) {
                $this->data = array_merge($this->data, $v);
            }
        }
    }

    /**
     * Assigns a value or an array of values to the data object.
     *
     * @param array|string $name an associative array of multiple (index=>value) or a string
     *                           that is the index to use, i.e. a value assigned to "foo" will be
     *                           accessible in the template through {$foo}
     * @param mixed        $val  the value to assign, or null if $name was an array
     */
    public function assign($name, $val = null)
    {
        if (is_array($name)) {
            reset($name);
            foreach ($name as $k => $v){
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$name] = $val;
        }
    }

    /**
     * Allows to assign variables using the object syntax.
     *
     * @param string $name  the variable name
     * @param string $value the value to assign to it
     */
    public function __set($name, $value)
    {
        $this->assign($name, $value);
    }

    /**
     * Assigns a value by reference to the data object.
     *
     * @param string $name the index to use, i.e. a value assigned to "foo" will be
     *                     accessible in the template through {$foo}
     * @param mixed  $val  the value to assign by reference
     */
    public function assignByRef($name, &$val)
    {
        $this->data[$name] = &$val;
    }

    /**
     * Appends values or an array of values to the data object.
     *
     * @param array|string $name  an associative array of multiple (index=>value) or a string
     *                            that is the index to use, i.e. a value assigned to "foo" will be
     *                            accessible in the template through {$foo}
     * @param mixed        $val   the value to assign, or null if $name was an array
     * @param bool         $merge true to merge data or false to append, defaults to false
     */
    public function append($name, $val = null, $merge = false)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                if (isset($this->data[$key]) && !is_array($this->data[$key])) {
                    settype($this->data[$key], 'array');
                }

                if ($merge === true && is_array($val)) {
                    $this->data[$key] = $val + $this->data[$key];
                } else {
                    $this->data[$key][] = $val;
                }
            }
        } elseif ($val !== null) {
            if (isset($this->data[$name]) && !is_array($this->data[$name])) {
                settype($this->data[$name], 'array');
            } elseif (!isset($this->data[$name])) {
                $this->data[$name] = array();
            }

            if ($merge === true && is_array($val)) {
                $this->data[$name] = $val + $this->data[$name];
            } else {
                $this->data[$name][] = $val;
            }
        }
    }

    /**
     * Appends a value by reference to the data object.
     *
     * @param string $name  the index to use, i.e. a value assigned to "foo" will be
     *                      accessible in the template through {$foo}
     * @param mixed  $val   the value to append by reference
     * @param bool   $merge true to merge data or false to append, defaults to false
     */
    public function appendByRef($name, &$val, $merge = false)
    {
        if (isset($this->data[$name]) && !is_array($this->data[$name])) {
            settype($this->data[$name], 'array');
        }

        if ($merge === true && is_array($val)) {
            foreach ($val as $key => &$value) {
                $this->data[$name][$key] = &$value;
            }
        } else {
            $this->data[$name][] = &$val;
        }
    }

    /**
     * Returns true if the variable has been assigned already, false otherwise.
     *
     * @param string $name the variable name
     *
     * @return bool
     */
    public function isAssigned($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Supports calls to isset($dwoo->var).
     *
     * @param string $name the variable name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Unassigns/removes a variable.
     *
     * @param string $name the variable name
     */
    public function unassign($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Supports unsetting variables using the object syntax.
     *
     * @param string $name the variable name
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Returns a variable if it was assigned.
     *
     * @param string $name the variable name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * Allows to read variables using the object syntax.
     *
     * @param string $name the variable name
     *
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            throw new Exception('Tried to read a value that was not assigned yet : "' . $name . '"');
        }
    }
}
