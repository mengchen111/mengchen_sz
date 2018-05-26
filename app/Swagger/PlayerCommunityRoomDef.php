<?php

/**
 * 社团房间，玩家数据模型
 *
 * @SWG\Definition(
 *   definition="GamePlayerCommunityRoom",
 *   description="社团房间，玩家数据模型",
 *   type="object",
 *   @SWG\Property(
 *       property="uid",
 *       description="玩家id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="nickname",
 *       description="玩家昵称",
 *       type="string",
 *       example="小明",
 *   ),
 *   @SWG\Property(
 *       property="headimg",
 *       description="头像",
 *       type="string",
 *       example="http://thirdwx.qlogo.cn/mmopen/vi_32/I8pkQrrk3vShL4iaAGHlI3Scib7mH6ibIa69oAvG27y9OwEtwETVGytsXTz3CfOzDA8jhd3eEu91bBePMcxHYLiadg/132",
 *   ),
 *   @SWG\Property(
 *       property="score",
 *       description="分数",
 *       type="integer",
 *       example=1,
 *   ),
 * )
 */