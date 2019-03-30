-- ----------------------------
-- 数据转换操作，oidc 午安账户表，movie 影视表
-- ----------------------------
-- ----------------------------
-- 将用户账号内的午安币以1000倍换算成午安果发放给用户
-- ----------------------------
REPLACE oidc.wuan_fruit (`user_id`,`value`) SELECT user_id,points*1000 FROM oidc.wuan_points;

-- ----------------------------
-- 将用户账号内的影视积分以250倍换算成午安果发放给用户
-- ----------------------------
UPDATE oidc.wuan_fruit set `value` = `value` + (SELECT points FROM movie.points WHERE movie.points.user_id = oidc.wuan_fruit.user_id)*250;