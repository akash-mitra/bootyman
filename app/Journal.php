<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = ['type', 'order_id', 'origin', 'code', 'message', 'user_id', 'context'];


    /**
     * Returns an ever increasing unique number that can be used as the lineage code.
     */
    public static function unique()
    {
        return time() * 10 + mt_rand(0, 9);
    }

    /**
     * Logs the entry as an incoming request in database table
     * @param string $message
     * @param integer $code
     * @param string $origin
     * @param string $order_id
     * @param array $context
     */
    public static function req(string $message, $code = null, string $origin = null, string $order_id = null, array $context = [])
    {
        return static::record('request', $message, $code, $origin, $order_id, $context);
    }

    /**
     * Logs the entry as an error in database table
     */
    public static function error(string $message, $code = null, string $origin = null, string $order_id = null, array $context = [])
    {
        return static::record('error', $message, $code, $origin, $order_id, $context);
    }


    /**
     * Logs the entry as an warning in database table
     */
    public static function warning(string $message, $code = null, string $origin = null, string $order_id = null, array $context = [])
    {
        return static::record('warning', $message, $code, $origin, $order_id, $context);
    }


    /**
     * Logs the entry as an information in the database table
     */
    public static function info(string $message, $code = null, string $origin = null, string $order_id = null, array $context = [])
    {
        return static::record('info', $message, $code, $origin, $order_id, $context);
    }

    /**
     * Logs an entry in the database table.
     */
    public static function record(
        $type,
        string $message,
        $code = null,
        string $origin = null,
        string $order_id = null,
        array $contextArray = []
    ) {
        $user_id = null;

        $user = auth()->user();

        if (!empty($user)) {
            $user_id = $user->id;
        }

        $context = json_encode($contextArray);

        $journal = new Journal(compact(['type', 'order_id', 'origin', 'code', 'message', 'user_id', 'context']));

        return $journal->save();
    }
}
