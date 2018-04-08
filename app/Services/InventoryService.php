<?php

namespace App\Services;

use App\Exceptions\InventoryServiceException;
use App\Models\User;

class InventoryService
{
    public static function addStock($recipientId, $itemType, $amount)
    {
        self::addStock4User($recipientId, $itemType, $amount);
    }

    public static function subStock($recipientId, $itemType, $amount)
    {
        self::subStock4User($recipientId, $itemType, $amount);
    }

    public static function addStock4User($userId, $itemType, $amount)
    {
        $user = User::with(['inventory' => function ($query) use ($itemType) {
            $query->where('item_id', $itemType);
        }])->find($userId);

        self::checkUserExists($user);

        if (empty($user->inventory)) {
            $user->inventory()->create([
                'user_id' => $user->id,
                'item_id' => $itemType,
                'stock' => $amount,
            ]);
        } else {
            $user->inventory->stock += $amount;
            $user->inventory->save();
        }
    }

    protected static function checkUserExists($user)
    {
        if (empty($user)) {
            throw new InventoryServiceException('用户不存在');
        }
    }

    public static function subStock4User($userId, $itemType, $amount)
    {
        $user = User::with(['inventory' => function ($query) use ($itemType) {
            $query->where('item_id', $itemType);
        }])->find($userId);

        self::checkUserExists($user);

        $user->inventory->stock -= $amount;
        $user->inventory->save();
    }

}