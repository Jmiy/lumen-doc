<?php

namespace App\Util\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Interface defining a client able to execute commands against Redis.
 *
 * All the commands exposed by the client generally have the same signature as
 * described by the Redis documentation, but some of them offer an additional
 * and more friendly interface to ease programming which is described in the
 * following list of methods:
 *
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class CacheManager extends Cache {

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
//    public static function __callStatic($method, $args)
//    {
//        $instance = static::getFacadeRoot();
//
//        if (!$instance) {
//            throw new RuntimeException('A facade root has not been set.');
//        }
//
//        $rs = null;
//        try {
//
//            dump(__METHOD__);
//            $rs = $instance->$method(...$args);
//            dump('======89898====');
//
//        } catch (\Exception $exc) {
//
//            dd($method);
//
//            $instance = Redis::getFacadeRoot();
//            $connection = $instance->connection('cache');
//
//            try {
//
//                $rs = $connection->$method(...$args);
//            } catch (\Exception $exc1) {
//                $instance->disconnect();
//                //throw $exc;
//            }
//
//        }
//
//        return $rs;
//    }

    public static function handle($method, $args)
    {
        $instance = Redis::getFacadeRoot();
        //$instance->select('1');

        return $instance->$method(...$args);
    }

    public static function del($keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function dump($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function exists($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function expire($key, $seconds)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function expireat($key, $timestamp)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function keys($pattern)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function move($key, $db)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function object($subcommand, $key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function persist($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Sets an expiration date (a timeout in milliseconds) on an item
     *
     * @param string $key The key that will disappear.
     * @param int    $ttl The key's remaining Time To Live, in milliseconds
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @link    https://redis.io/commands/pexpire
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->pexpire('x', 11500); // x will disappear in 11500 milliseconds.
     * $redis->ttl('x');            // 12
     * $redis->pttl('x');           // 11500
     * </pre>
     */
    public static function pexpire($key, $milliseconds)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Sets an expiration date (a timestamp) on an item. Requires a timestamp in milliseconds
     *
     * @param string $key       The key that will disappear
     * @param int    $timestamp Unix timestamp. The key's date of death, in seconds from Epoch time
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @link    https://redis.io/commands/pexpireat
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->pexpireat('x', 1555555555005);
     * echo $redis->ttl('x');                       // 218270121
     * echo $redis->pttl('x');                      // 218270120575
     * </pre>
     */
    public static function pexpireat($key, $timestamp)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function pttl($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function randomkey()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function rename($key, $target)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function renamenx($key, $target)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function scan($cursor, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sort($key, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function ttl($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function type($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function append($key, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function bitcount($key, $start = null, $end = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function bitop($operation, $destkey, $key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function bitfield($key, $subcommand, ...$subcommandArg)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function bitpos($key, $bit, $start = null, $end = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function decr($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function decrby($key, $decrement)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function get($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function getbit($key, $offset)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function getrange($key, $start, $end)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function getset($key, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function incr($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function incrby($key, $increment)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function incrbyfloat($key, $increment)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function mget(array $keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function mset(array $dictionary)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function msetnx(array $dictionary)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function psetex($key, $milliseconds, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Changes a single bit of a string.
     *
     * @param string   $key
     * @param int      $offset
     * @param bool|int $value  bool or int (1 or 0)
     * @param int $seconds  缓存时间  单位秒(支持：0.02)
     *
     * @return int 0 or 1, the value of the bit before it was set
     *
     * @link    https://redis.io/commands/setbit
     * @example
     * <pre>
     * $redis->set('key', "*");     // ord("*") = 42 = 0x2f = "0010 1010"
     * $redis->setBit('key', 5, 1); // returns 0
     * $redis->setBit('key', 7, 1); // returns 0
     * $redis->get('key');          // chr(0x2f) = "/" = b("0010 1111")
     * </pre>
     */
    public static function setbit($key, $offset, $value, $seconds = null)
    {
        $instance = Redis::getFacadeRoot();

        if ($seconds !== null) {

            $instance->multi();

            $instance->setbit($key, $offset, $value);

            $instance->pexpire($key, $seconds * 1000);

            $manyResult = $instance->exec();

            $result = [
                'result' => data_get($manyResult, 0),
                'pexpire' => data_get($manyResult, 1),
            ];

        } else {
            $result = $instance->setbit($key, $offset, $value);
        }

        return $result;
    }

    public static function setex($key, $seconds, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function setnx($key, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function setrange($key, $offset, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function strlen($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Removes a values from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey1
     * @param string ...$otherHashKeys
     *
     * @return int|bool Number of deleted fields
     *
     * @link    https://redis.io/commands/hdel
     * @example
     * <pre>
     * $redis->hMSet('h',
     *               array(
     *                    'f1' => 'v1',
     *                    'f2' => 'v2',
     *                    'f3' => 'v3',
     *                    'f4' => 'v4',
     *               ));
     *
     * var_dump( $redis->hDel('h', 'f1') );        // int(1)
     * var_dump( $redis->hDel('h', 'f2', 'f3') );  // int(2)
     * s
     * var_dump( $redis->hGetAll('h') );
     * //// Output:
     * //  array(1) {
     * //    ["f4"]=> string(2) "v4"
     * //  }
     * </pre>
     */
    public static function hdel($key, array $fields)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Verify if the specified member exists in a key.
     *
     * @param string $key
     * @param string $hashKey
     *
     * @return bool If the member exists in the hash table, return TRUE, otherwise return FALSE.
     *
     * @link    https://redis.io/commands/hexists
     * @example
     * <pre>
     * $redis->hSet('h', 'a', 'x');
     * $redis->hExists('h', 'a');               //  TRUE
     * $redis->hExists('h', 'NonExistingKey');  // FALSE
     * </pre>
     */
    public static function hexists($key, $field)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Gets a value from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey
     *
     * @return string The value, if the command executed successfully BOOL FALSE in case of failure
     *
     * @link    https://redis.io/commands/hget
     */
    public static function hget($key, $field)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the whole hash, as an array of strings indexed by strings.
     *
     * @param string $key
     *
     * @return array An array of elements, the contents of the hash.
     *
     * @link    https://redis.io/commands/hgetall
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'a', 'x');
     * $redis->hSet('h', 'b', 'y');
     * $redis->hSet('h', 'c', 'z');
     * $redis->hSet('h', 'd', 't');
     * var_dump($redis->hGetAll('h'));
     *
     * // Output:
     * // array(4) {
     * //   ["a"]=>
     * //   string(1) "x"
     * //   ["b"]=>
     * //   string(1) "y"
     * //   ["c"]=>
     * //   string(1) "z"
     * //   ["d"]=>
     * //   string(1) "t"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function hgetall($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Increments the value of a member from a hash by a given amount.
     *
     * @param string $key
     * @param string $hashKey
     * @param int    $value (integer) value that will be added to the member's value
     *
     * @return int the new value
     *
     * @link    https://redis.io/commands/hincrby
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hIncrBy('h', 'x', 2); // returns 2: h[x] = 2 now.
     * $redis->hIncrBy('h', 'x', 1); // h[x] ← 2 + 1. Returns 3
     * </pre>
     */
    public static function hincrby($key, $field, $increment)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function hmincrby($key, array $values)
    {
        $instance = Redis::getFacadeRoot();
        //$instance->select('1');

        $instance->multi();

        $_manyResult = [];
        foreach ($values as $field => $increment) {
            $instance->hincrby($key, $field, $increment);
            $_manyResult[] = $field;
        }
        $exeResult = $instance->exec();

        $manyResult = [];
        foreach ($exeResult as $index => $result) {
            $manyResult[data_get($_manyResult, $index)] = $result;
        }

        unset($_manyResult);

        return $manyResult;
    }

    /**
     * Increment the float value of a hash field by the given amount
     *
     * @param string $key
     * @param string $field
     * @param float  $increment
     *
     * @return float
     *
     * @link    https://redis.io/commands/hincrbyfloat
     * @example
     * <pre>
     * $redis = new Redis();
     * $redis->connect('127.0.0.1');
     * $redis->hset('h', 'float', 3);
     * $redis->hset('h', 'int',   3);
     * var_dump( $redis->hIncrByFloat('h', 'float', 1.5) ); // float(4.5)
     *
     * var_dump( $redis->hGetAll('h') );
     *
     * // Output
     *  array(2) {
     *    ["float"]=>
     *    string(3) "4.5"
     *    ["int"]=>
     *    string(1) "3"
     *  }
     * </pre>
     */
    public static function hincrbyfloat($key, $field, $increment)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the keys in a hash, as an array of strings.
     *
     * @param string $key
     *
     * @return array An array of elements, the keys of the hash. This works like PHP's array_keys().
     *
     * @link    https://redis.io/commands/hkeys
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'a', 'x');
     * $redis->hSet('h', 'b', 'y');
     * $redis->hSet('h', 'c', 'z');
     * $redis->hSet('h', 'd', 't');
     * var_dump($redis->hKeys('h'));
     *
     * // Output:
     * // array(4) {
     * // [0]=>
     * // string(1) "a"
     * // [1]=>
     * // string(1) "b"
     * // [2]=>
     * // string(1) "c"
     * // [3]=>
     * // string(1) "d"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function hkeys($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the length of a hash, in number of items
     *
     * @param string $key
     *
     * @return int|bool the number of items in a hash, FALSE if the key doesn't exist or isn't a hash
     *
     * @link    https://redis.io/commands/hlen
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSet('h', 'key1', 'hello');
     * $redis->hSet('h', 'key2', 'plop');
     * $redis->hLen('h'); // returns 2
     * </pre>
     */
    public static function hlen($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Retirieve the values associated to the specified fields in the hash.
     *
     * @param string $key
     * @param array  $hashKeys
     *
     * @return array Array An array of elements, the values of the specified fields in the hash,
     * with the hash keys as array keys.
     *
     * @link    https://redis.io/commands/hmget
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'field1', 'value1');
     * $redis->hSet('h', 'field2', 'value2');
     * $redis->hmGet('h', array('field1', 'field2')); // returns array('field1' => 'value1', 'field2' => 'value2')
     * </pre>
     */
    public static function hmget($key, array $fields)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast.
     * NULL values are stored as empty strings
     *
     * @param string $key
     * @param array  $hashKeys key → value array
     * @param int $seconds  缓存时间  单位秒(支持：0.02)
     *
     * @return bool|array
     *
     * @link    https://redis.io/commands/hmset
     * @example
     * <pre>
     * $redis->del('user:1');
     * $redis->hMSet('user:1', array('name' => 'Joe', 'salary' => 2000));
     * $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
     * </pre>
     */
    public static function hmset($key, array $dictionary, $seconds = null)
    {

        if($seconds === null){
            return static::handle(__FUNCTION__, [$key, $dictionary]);
        }

        $instance = Redis::getFacadeRoot();
        //$instance->select('1');

        $instance->multi();
        $instance->hmset($key, $dictionary);
        $instance->pexpire($key, $seconds * 1000);
        $manyResult = $instance->exec();

//        $connection = $instance->connection('cache');
//
//        $connection->multi();
//
//        $manyResult = $connection->hmset($key, $dictionary);
//
//        if ($seconds !== null) {
//            $result = $connection->pexpire($key, $seconds * 1000);
//            $manyResult = $result && $manyResult;
//        }
//
//        $manyResult = $connection->exec();
//
//        $instance->disconnect();

        return [
            'result' => data_get($manyResult, 0),
            'pexpire' => data_get($manyResult, 1),
        ];
    }

    /**
     * Scan a HASH value for members, with an optional pattern and count.
     *
     * @param string $key
     * @param int    $iterator
     * @param string $pattern    Optional pattern to match against.
     * @param int    $count      How many keys to return in a go (only a sugestion to Redis).
     *
     * @return array An array of members that match our pattern.
     *
     * @link    https://redis.io/commands/hscan
     * @example
     * <pre>
     * // $iterator = null;
     * // while($elements = $redis->hscan('hash', $iterator)) {
     * //     foreach($elements as $key => $value) {
     * //         echo $key . ' => ' . $value . PHP_EOL;
     * //     }
     * // }
     * </pre>
     */
    public static function hscan($key, $cursor, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Adds a value to the hash stored at key. If this value is already in the hash, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey
     * @param string $value
     *
     * @return int|bool
     * - 1 if value didn't exist and was added successfully,
     * - 0 if the value was already present and was replaced, FALSE if there was an error.
     *
     * @link    https://redis.io/commands/hset
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSet('h', 'key1', 'hello');  // 1, 'key1' => 'hello' in the hash at "h"
     * $redis->hGet('h', 'key1');           // returns "hello"
     *
     * $redis->hSet('h', 'key1', 'plop');   // 0, value was replaced.
     * $redis->hGet('h', 'key1');           // returns "plop"
     * </pre>
     */
    public static function hset($key, $field, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Adds a value to the hash stored at key only if this field isn't already in the hash.
     *
     * @param string $key
     * @param string $hashKey
     * @param string $value
     *
     * @return  bool TRUE if the field was set, FALSE if it was already present.
     *
     * @link    https://redis.io/commands/hsetnx
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSetNx('h', 'key1', 'hello'); // TRUE, 'key1' => 'hello' in the hash at "h"
     * $redis->hSetNx('h', 'key1', 'world'); // FALSE, 'key1' => 'hello' in the hash at "h". No change since the field
     * wasn't replaced.
     * </pre>
     */
    public static function hsetnx($key, $field, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the values in a hash, as an array of strings.
     *
     * @param string $key
     *
     * @return array An array of elements, the values of the hash. This works like PHP's array_values().
     *
     * @link    https://redis.io/commands/hvals
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'a', 'x');
     * $redis->hSet('h', 'b', 'y');
     * $redis->hSet('h', 'c', 'z');
     * $redis->hSet('h', 'd', 't');
     * var_dump($redis->hVals('h'));
     *
     * // Output
     * // array(4) {
     * //   [0]=>
     * //   string(1) "x"
     * //   [1]=>
     * //   string(1) "y"
     * //   [2]=>
     * //   string(1) "z"
     * //   [3]=>
     * //   string(1) "t"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function hvals($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Get the string length of the value associated with field in the hash stored at key
     *
     * @param string $key
     * @param string $field
     *
     * @return int the string length of the value associated with field, or zero when field is not present in the hash
     * or key does not exist at all.
     *
     * @link https://redis.io/commands/hstrlen
     * @since >= 3.2
     */
    public static function hstrlen($key, $field)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 移出并获取列表的第一个元素， 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     * Is a blocking lPop primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * Il all the list identified by the keys passed in arguments are empty, blPop will block
     * during the specified timeout until an element is pushed to one of those lists. This element will be popped.
     *
     * @param string|string[] $keys    String array containing the keys of the lists OR variadic list of strings
     * @param int             $timeout Timeout is always the required final parameter
     *
     * @return array ['listName', 'element']
     *
     * @link    https://redis.io/commands/blpop
     * @example
     * <pre>
     * // Non blocking feature
     * $redis->lPush('key1', 'A');
     * $redis->del('key2');
     *
     * $redis->blPop('key1', 'key2', 10);        // array('key1', 'A')
     * // OR
     * $redis->blPop(['key1', 'key2'], 10);      // array('key1', 'A')
     *
     * $redis->brPop('key1', 'key2', 10);        // array('key1', 'A')
     * // OR
     * $redis->brPop(['key1', 'key2'], 10); // array('key1', 'A')
     *
     * // Blocking feature
     *
     * // process 1
     * $redis->del('key1');
     * $redis->blPop('key1', 10);
     * // blocking for 10 seconds
     *
     * // process 2
     * $redis->lPush('key1', 'A');
     *
     * // process 1
     * // array('key1', 'A') is returned
     * </pre>
     */
    public static function blpop($keys, $timeout)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 移出并获取列表的最后一个元素， 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     * Is a blocking rPop primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * Il all the list identified by the keys passed in arguments are empty, brPop will
     * block during the specified timeout until an element is pushed to one of those lists. T
     * his element will be popped.
     *
     * @param string|string[] $keys    String array containing the keys of the lists OR variadic list of strings
     * @param int             $timeout Timeout is always the required final parameter
     *
     * @return array ['listName', 'element']
     *
     * @link    https://redis.io/commands/brpop
     * @example
     * <pre>
     * // Non blocking feature
     * $redis->lPush('key1', 'A');
     * $redis->del('key2');
     *
     * $redis->blPop('key1', 'key2', 10); // array('key1', 'A')
     * // OR
     * $redis->blPop(array('key1', 'key2'), 10); // array('key1', 'A')
     *
     * $redis->brPop('key1', 'key2', 10); // array('key1', 'A')
     * // OR
     * $redis->brPop(array('key1', 'key2'), 10); // array('key1', 'A')
     *
     * // Blocking feature
     *
     * // process 1
     * $redis->del('key1');
     * $redis->blPop('key1', 10);
     * // blocking for 10 seconds
     *
     * // process 2
     * $redis->lPush('key1', 'A');
     *
     * // process 1
     * // array('key1', 'A') is returned
     * </pre>
     */
    public static function brpop($keys, $timeout)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 从列表中弹出一个值，将弹出的元素插入到另外一个列表中并返回它； 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     * A blocking version of rpoplpush, with an integral timeout in the third parameter.
     *
     * @param string $srcKey
     * @param string $dstKey
     * @param int    $timeout
     *
     * @return  string|mixed|bool  The element that was moved in case of success, FALSE in case of timeout
     *
     * @link    https://redis.io/commands/brpoplpush
     */
    public static function brpoplpush($source, $destination, $timeout)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 通过索引获取列表中的元素
     * Return the specified element of the list stored at the specified key.
     * 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * Return FALSE in case of a bad index or a key that doesn't point to a list.
     *
     * @param string $key
     * @param int    $index
     *
     * @return mixed|bool the element at this index
     *
     * Bool FALSE if the key identifies a non-string data type, or no value corresponds to this index in the list Key.
     *
     * @link    https://redis.io/commands/lindex
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * $redis->lIndex('key1', 0);     // 'A'
     * $redis->lIndex('key1', -1);    // 'C'
     * $redis->lIndex('key1', 10);    // `FALSE`
     * </pre>
     */
    public static function lindex($key, $index)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 在列表的元素前或者后插入元素
     * Insert value in the list before or after the pivot value. the parameter options
     * specify the position of the insert (before or after). If the list didn't exists,
     * or the pivot didn't exists, the value is not inserted.
     *
     * @param string       $key
     * @param int          $position Redis::BEFORE | Redis::AFTER
     * @param string       $pivot
     * @param string|mixed $value
     *
     * @return int The number of the elements in the list, -1 if the pivot didn't exists.
     *
     * @link    https://redis.io/commands/linsert
     * @example
     * <pre>
     * $redis->del('key1');
     * $redis->lInsert('key1', Redis::AFTER, 'A', 'X');     // 0
     *
     * $redis->lPush('key1', 'A');
     * $redis->lPush('key1', 'B');
     * $redis->lPush('key1', 'C');
     *
     * $redis->lInsert('key1', Redis::BEFORE, 'C', 'X');    // 4
     * $redis->lRange('key1', 0, -1);                       // array('A', 'B', 'X', 'C')
     *
     * $redis->lInsert('key1', Redis::AFTER, 'C', 'Y');     // 5
     * $redis->lRange('key1', 0, -1);                       // array('A', 'B', 'X', 'C', 'Y')
     *
     * $redis->lInsert('key1', Redis::AFTER, 'W', 'value'); // -1
     * </pre>
     */
    public static function linsert($key, $whence, $pivot, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 获取列表长度
     * Returns the size of a list identified by Key. If the list didn't exist or is empty,
     * the command returns 0. If the data type identified by Key is not a list, the command return FALSE.
     *
     * @param string $key
     *
     * @return int|bool The size of the list identified by Key exists.
     * bool FALSE if the data type identified by Key is not list
     *
     * @link    https://redis.io/commands/llen
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C'); // key1 => [ 'A', 'B', 'C' ]
     * $redis->lLen('key1');       // 3
     * $redis->rPop('key1');
     * $redis->lLen('key1');       // 2
     * </pre>
     */
    public static function llen($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 移出并获取列表的第一个元素
     * Returns and removes the first element of the list.
     *
     * @param   string $key
     *
     * @return  mixed|bool if command executed successfully BOOL FALSE in case of failure (empty list)
     *
     * @link    https://redis.io/commands/lpop
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * $redis->lPop('key1');        // key1 => [ 'B', 'C' ]
     * </pre>
     */
    public static function lpop($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 将一个或多个值插入到列表头部
     * Adds the string values to the head (left) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string $key
     * @param string|mixed $value1... Variadic list of values to push in key, if dont used serialized, used string
     *
     * @return int|bool The new length of the list in case of success, FALSE in case of Failure
     *
     * @link https://redis.io/commands/lpush
     * @example
     * <pre>
     * $redis->lPush('l', 'v1', 'v2', 'v3', 'v4')   // int(4)
     * var_dump( $redis->lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v4"
     * //   [1]=> string(2) "v3"
     * //   [2]=> string(2) "v2"
     * //   [3]=> string(2) "v1"
     * // }
     * </pre>
     */
    public static function lpush($key, array $values)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 将一个值插入到已存在的列表头部
     * Adds the string value to the head (left) of the list if the list exists.
     *
     * @param string $key
     * @param string|mixed $value String, value to push in key
     *
     * @return int|bool The new length of the list in case of success, FALSE in case of Failure.
     *
     * @link    https://redis.io/commands/lpushx
     * @example
     * <pre>
     * $redis->del('key1');
     * $redis->lPushx('key1', 'A');     // returns 0
     * $redis->lPush('key1', 'A');      // returns 1
     * $redis->lPushx('key1', 'B');     // returns 2
     * $redis->lPushx('key1', 'C');     // returns 3
     * // key1 now points to the following list: [ 'A', 'B', 'C' ]
     * </pre>
     */
    public static function lpushx($key, array $values)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 获取列表指定范围内的元素
     * Returns the specified elements of the list stored at the specified key in
     * the range [start, end]. start and stop are interpretated as indices: 0 the first element,
     * 1 the second ... -1 the last element, -2 the penultimate ...
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return array containing the values in specified range.
     *
     * @link    https://redis.io/commands/lrange
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');
     * $redis->lRange('key1', 0, -1); // array('A', 'B', 'C')
     * </pre>
     */
    public static function lrange($key, $start, $stop)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 移除列表元素
     * Removes the first count occurrences of the value element from the list.
     * If count is zero, all the matching elements are removed. If count is negative,
     * elements are removed from tail to head.
     *
     * @param string $key
     * @param int    $count
     * @param string $value
     *
     * @return int|bool the number of elements to remove
     * bool FALSE if the value identified by key is not a list.
     *
     * @link    https://redis.io/commands/lrem
     * @example
     * <pre>
     * $redis->lPush('key1', 'A');
     * $redis->lPush('key1', 'B');
     * $redis->lPush('key1', 'C');
     * $redis->lPush('key1', 'A');
     * $redis->lPush('key1', 'A');
     *
     * $redis->lRange('key1', 0, -1);   // array('A', 'A', 'C', 'B', 'A')
     * $redis->lRem('key1', 'A', 2);    // 2
     * $redis->lRange('key1', 0, -1);   // array('C', 'B', 'A')
     * </pre>
     */
    public static function lrem($key, $count, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 通过索引设置列表元素的值
     * Set the list at index with the new value.
     *
     * @param string $key
     * @param int    $index
     * @param string $value
     *
     * @return bool TRUE if the new value is setted.
     * FALSE if the index is out of range, or data type identified by key is not a list.
     *
     * @link    https://redis.io/commands/lset
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');    // key1 => [ 'A', 'B', 'C' ]
     * $redis->lIndex('key1', 0);     // 'A'
     * $redis->lSet('key1', 0, 'X');
     * $redis->lIndex('key1', 0);     // 'X'
     * </pre>
     */
    public static function lset($key, $index, $value)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。
     * Trims an existing list so that it will contain only a specified range of elements.
     *
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return array|bool Bool return FALSE if the key identify a non-list value
     *
     * @link        https://redis.io/commands/ltrim
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');
     * $redis->lRange('key1', 0, -1); // array('A', 'B', 'C')
     * $redis->lTrim('key1', 0, 1);
     * $redis->lRange('key1', 0, -1); // array('A', 'B')
     * </pre>
     */
    public static function ltrim($key, $start, $stop)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 移除列表的最后一个元素，返回值为移除的元素。
     * Returns and removes the last element of the list.
     *
     * @param   string $key
     *
     * @return  mixed|bool if command executed successfully BOOL FALSE in case of failure (empty list)
     *
     * @link    https://redis.io/commands/rpop
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * $redis->rPop('key1');        // key1 => [ 'A', 'B' ]
     * </pre>
     */
    public static function rpop($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 移除列表的最后一个元素，并将该元素添加到另一个列表并返回
     * Pops a value from the tail of a list, and pushes it to the front of another list.
     * Also return this value.
     *
     * @since   redis >= 1.1
     *
     * @param string $srcKey
     * @param string $dstKey
     *
     * @return string|mixed|bool The element that was moved in case of success, FALSE in case of failure.
     *
     * @link    https://redis.io/commands/rpoplpush
     * @example
     * <pre>
     * $redis->del('x', 'y');
     *
     * $redis->lPush('x', 'abc');
     * $redis->lPush('x', 'def');
     * $redis->lPush('y', '123');
     * $redis->lPush('y', '456');
     *
     * // move the last of x to the front of y.
     * var_dump($redis->rpoplpush('x', 'y'));
     * var_dump($redis->lRange('x', 0, -1));
     * var_dump($redis->lRange('y', 0, -1));
     *
     * //Output:
     * //
     * //string(3) "abc"
     * //array(1) {
     * //  [0]=>
     * //  string(3) "def"
     * //}
     * //array(3) {
     * //  [0]=>
     * //  string(3) "abc"
     * //  [1]=>
     * //  string(3) "456"
     * //  [2]=>
     * //  string(3) "123"
     * //}
     * </pre>
     */
    public static function rpoplpush($source, $destination)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 在列表末尾添加一个或多个值
     * Adds the string values to the tail (right) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string $key
     * @param string|mixed $value1... Variadic list of values to push in key, if dont used serialized, used string
     *
     * @return int|bool The new length of the list in case of success, FALSE in case of Failure
     *
     * @link    https://redis.io/commands/rpush
     * @example
     * <pre>
     * $redis->rPush('l', 'v1', 'v2', 'v3', 'v4');    // int(4)
     * var_dump( $redis->lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v2"
     * //   [2]=> string(2) "v3"
     * //   [3]=> string(2) "v4"
     * // }
     * </pre>
     */
    public static function rpush($key, $values)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * 在已存在的列表末尾添加值
     * Adds the string value to the tail (right) of the list if the ist exists. FALSE in case of Failure.
     *
     * @param string $key
     * @param string|mixed $value String, value to push in key
     *
     * @return int|bool The new length of the list in case of success, FALSE in case of Failure.
     *
     * @link    https://redis.io/commands/rpushx
     * @example
     * <pre>
     * $redis->del('key1');
     * $redis->rPushx('key1', 'A'); // returns 0
     * $redis->rPush('key1', 'A'); // returns 1
     * $redis->rPushx('key1', 'B'); // returns 2
     * $redis->rPushx('key1', 'C'); // returns 3
     * // key1 now points to the following list: [ 'A', 'B', 'C' ]
     * </pre>
     */
    public static function rpushx($key, $values)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sadd($key, $members)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function scard($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sdiff($keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sdiffstore($destination, $keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sinter($keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sinterstore($destination, $keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sismember($key, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function smembers($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function smove($source, $destination, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function spop($key, $count = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function srandmember($key, $count = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function srem($key, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sscan($key, $cursor, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sunion($keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function sunionstore($destination, $keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zadd($key, array $membersAndScoresDictionary)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zcard($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zcount($key, $min, $max)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zincrby($key, $increment, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zinterstore($destination, $keys, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrange($key, $start, $stop, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrangebyscore($key, $min, $max, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrank($key, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrem($key, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zremrangebyrank($key, $start, $stop)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zremrangebyscore($key, $min, $max)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrevrange($key, $start, $stop, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrevrangebyscore($key, $max, $min, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrevrank($key, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zunionstore($destination, $keys, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zscore($key, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zscan($key, $cursor, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrangebylex($key, $start, $stop, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zrevrangebylex($key, $start, $stop, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zremrangebylex($key, $min, $max)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function zlexcount($key, $min, $max)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function pfadd($key, array $elements)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function pfmerge($destinationKey, $sourceKeys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function pfcount($keys)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function pubsub($subcommand, $argument)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function publish($channel, $message)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function discard()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * @return void|array
     *
     * @see multi()
     * @link https://redis.io/commands/exec
     */
    public static function exec()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    /**
     * Enter and exit transactional mode.
     *
     * @param int $mode Redis::MULTI|Redis::PIPELINE
     * Defaults to Redis::MULTI.
     * A Redis::MULTI block of commands runs as a single transaction;
     * a Redis::PIPELINE block is simply transmitted faster to the server, but without any guarantee of atomicity.
     * discard cancels a transaction.
     *
     * @return Redis returns the Redis instance and enters multi-mode.
     * Once in multi-mode, all subsequent method calls return the same object until exec() is called.
     *
     * @link    https://redis.io/commands/multi
     * @example
     * <pre>
     * $ret = $redis->multi()
     *      ->set('key1', 'val1')
     *      ->get('key1')
     *      ->set('key2', 'val2')
     *      ->get('key2')
     *      ->exec();
     *
     * //$ret == array (
     * //    0 => TRUE,
     * //    1 => 'val1',
     * //    2 => TRUE,
     * //    3 => 'val2');
     * </pre>
     */
    public static function multi()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function unwatch()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function watch($key)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function eval($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function evalsha($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function script($subcommand, $argument = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function auth($password)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function echo($message)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function ping($message = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function select($database)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function bgrewriteaof()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function bgsave()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function client($subcommand, $argument = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function config($subcommand, $argument = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function dbsize()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function flushall()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function flushdb()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function info($section = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function lastsave()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function save()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function slaveof($host, $port)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function slowlog($subcommand, $argument = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function time()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function command()
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function geoadd($key, $longitude, $latitude, $member)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function geohash($key, array $members)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function geopos($key, array $members)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function geodist($key, $member1, $member2, $unit = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function georadius($key, $longitude, $latitude, $radius, $unit, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

    public static function georadiusbymember($key, $member, $radius, $unit, array $options = null)
    {
        return static::handle(__FUNCTION__, func_get_args());
    }

//    /**
//     * Handle dynamic, static calls to the object.
//     *
//     * @param  string  $method
//     * @param  array   $args
//     * @return mixed
//     *
//     * @throws \RuntimeException
//     */
//    public static function __callStatic($method, $args) {
//        return Cache::$method(...$args);
//    }
//
//    /**
//     * Begin executing a new tags operation.
//     *
//     * @param  array|mixed  $names
//     * @return \Illuminate\Cache\RedisTaggedCache
//     */
//    public static function tags($names) {
//        return Cache::tags($names);
//    }
//
//    /**
//     * Retrieve an item from the cache by key.
//     *
//     * @param  string  $key
//     * @return mixed
//     */
//    public static function get($key)
//    {
//        return Cache::get($key);
//    }
//
//    /**
//     * Retrieve multiple items from the cache by key.
//     *
//     * Items not found in the cache will have a null value.
//     *
//     * @param  array  $keys
//     * @return array
//     */
//    public static function many(array $keys)
//    {
//        return Cache::many($key);
//    }
//
//    /**
//     * Store an item in the cache for a given number of seconds.
//     *
//     * @param  string  $key
//     * @param  mixed   $value
//     * @param  int  $seconds
//     * @return bool
//     */
//    public static function put($key, $value, $seconds)
//    {
//        return Cache::put($key, $value, $seconds);
//    }
//
//    /**
//     * Store multiple items in the cache for a given number of seconds.
//     *
//     * @param  array  $values
//     * @param  int  $seconds
//     * @return bool
//     */
//    public static function putMany(array $values, $seconds)
//    {
//        return Cache::putMany($values, $seconds);
//    }
//
//    /**
//     * Store an item in the cache if the key doesn't exist.
//     *
//     * @param  string  $key
//     * @param  mixed   $value
//     * @param  int  $seconds
//     * @return bool
//     */
//    public static function add($key, $value, $seconds)
//    {
//        return Cache::putMany($key, $value, $seconds);
//    }
//
//    /**
//     * Increment the value of an item in the cache.
//     *
//     * @param  string  $key
//     * @param  mixed   $value
//     * @return int|bool
//     */
//    public static function increment($key, $value = 1)
//    {
//        return Cache::increment($key, $value);
//    }
//
//    /**
//     * Decrement the value of an item in the cache.
//     *
//     * @param  string  $key
//     * @param  mixed   $value
//     * @return int|bool
//     */
//    public static function decrement($key, $value = 1)
//    {
//        return Cache::decrement($key, $value);
//    }
//
//    /**
//     * Store an item in the cache indefinitely.
//     *
//     * @param  string  $key
//     * @param  mixed   $value
//     * @return bool
//     */
//    public static function forever($key, $value)
//    {
//        return Cache::forever($key, $value);
//    }
//
//    /**
//     * Remove an item from the cache.
//     *
//     * @param  string  $key
//     * @return bool
//     */
//    public static function forget($key)
//    {
//        return Cache::forget($key);
//    }
//
//    /**
//     * Remove all items from the cache.
//     *
//     * @return bool
//     */
//    public static function flush()
//    {
//        return Cache::flush();
//    }
//
//    /**
//     * Get the cache key prefix.
//     *
//     * @return string
//     */
//    public static function getPrefix()
//    {
//        return Cache::getPrefix();
//    }
}
