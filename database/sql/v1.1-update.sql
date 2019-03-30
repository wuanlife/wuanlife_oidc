-- ----------------------------
-- Table structure for wuan_fruit
-- ----------------------------
CREATE TABLE `wuan_fruit`  (
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `value` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '午安果',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '午安果' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wuan_sign
-- ----------------------------
CREATE TABLE `wuan_sign`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `value` int(10) UNSIGNED NOT NULL COMMENT '本次签到赠送的午安果数量',
  `created_at` timestamp(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '签到记录表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
