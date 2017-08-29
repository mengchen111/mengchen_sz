<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationLogs extends Model
{
    protected $table = 'operation_logs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'uri', 'method', 'description', 'data'
    ];

    public static function insert($userId, $uri, $method, $description, $data)
    {
        return self::create([
            'user_id' => $userId,
            'uri' => $uri,
            'method' => $method,
            'description' => $description,
            'data' => $data
        ]);
    }
}