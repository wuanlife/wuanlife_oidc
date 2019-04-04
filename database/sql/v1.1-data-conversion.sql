-- ----------------------------
-- 数据转换操作，oidc 午安账户库，movie 影视库
-- ----------------------------
-- ----------------------------
-- 将用户账号内的午安币以1000倍换算成午安果发放给用户，并新增记录
-- ----------------------------
REPLACE oidc.wuan_fruit (`user_id`,`value`) SELECT user_id,points*1000 FROM oidc.wuan_points;

-- ----------------------------
-- 将用户账号内的影视积分以250倍换算成午安果发放给用户
-- ----------------------------
UPDATE oidc.wuan_fruit set `value` = `value` + (SELECT points FROM movie.points WHERE movie.points.user_id = oidc.wuan_fruit.user_id)*250;

-- ----------------------------
-- 原午安币转换,午安果变动记录
-- ----------------------------
INSERT INTO oidc.wuan_fruit_log (`scene`,`user_id`,`value`,`created_at`) SELECT 1,user_id,points*1000,current_timestamp() FROM oidc.wuan_points where points > 0;

-- ----------------------------
-- 影视积分转换,午安果变动记录
-- ----------------------------
INSERT INTO oidc.wuan_fruit_log (`scene`,`user_id`,`value`,`created_at`) SELECT 1,user_id,points*250,current_timestamp() FROM movie.points where points > 0;