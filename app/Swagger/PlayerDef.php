<?php

/**
 *
 * @SWG\Definition(
 *   definition="GamePlayer",
 *   description="游戏玩家模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="unionid",
 *       description="玩家微信unionid",
 *       type="string",
 *       example="ope1JuMNKR7NFGEHYxeyfYBb0nQE",
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
 *       property="city",
 *       description="城市",
 *       type="string",
 *       example="Shenzhen",
 *   ),
 *   @SWG\Property(
 *       property="gender",
 *       description="性别（1-男,2-女）",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="ycoins",
 *       description="房卡",
 *       type="integer",
 *       format="int32",
 *       example=29,
 *   ),
 *   @SWG\Property(
 *       property="ypoints",
 *       description="金币",
 *       type="integer",
 *       format="int32",
 *       example=0,
 *   ),
 *   @SWG\Property(
 *       property="state",
 *       description="账号当前状态",
 *       type="integer",
 *       format="int32",
 *       example=0,
 *   ),
 *   @SWG\Property(
 *       property="create_time",
 *       description="创建时间",
 *       type="string",
 *       example="2018-03-30 16:03:14",
 *   ),
 *   @SWG\Property(
 *       property="last_time",
 *       description="最近登陆时间",
 *       type="string",
 *       example="2018-03-30 17:14:42",
 *   ),
 *   @SWG\Property(
 *       property="invitation_code",
 *       description="邀请码",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 * )
 *
 */