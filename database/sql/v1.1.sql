/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50724
 Source Host           : localhost:3306
 Source Schema         : forge

 Target Server Type    : MySQL
 Target Server Version : 50724
 File Encoding         : 65001

 Date: 02/04/2019 20:11:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for app_point_detail
-- ----------------------------
CREATE TABLE `app_point_detail`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '标识id',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '应用名',
  `exchange_rate` float NOT NULL COMMENT '兑换汇率',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '该应用的地址',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '子应用积分系统详情表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for auth_detail
-- ----------------------------
CREATE TABLE `auth_detail`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '权限id(偏移量)',
  `indentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '权限类型',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `indentity`(`indentity`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '权限对应关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for avatar_url
-- ----------------------------
CREATE TABLE `avatar_url`  (
  `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '图片url',
  `delete_flg` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片是否已删除',
  INDEX `image_user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '用户头像url表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for points_order
-- ----------------------------
CREATE TABLE `points_order`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `points_alert` float NOT NULL COMMENT '午安影视积分',
  `created_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '积分兑换记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for reset_password
-- ----------------------------
CREATE TABLE `reset_password`  (
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL COMMENT '验证token',
  `created_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exp` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '过期时间',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '找回密码表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sex_detail
-- ----------------------------
CREATE TABLE `sex_detail`  (
  `id` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '性别标识(英文)',
  `sex` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '性别类型',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sex_detail_index`(`sex`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '性别对应关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users_auth
-- ----------------------------
CREATE TABLE `users_auth`  (
  `id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `name` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '用户名',
  `auth` int(10) UNSIGNED NOT NULL COMMENT '用户权限',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `auth`(`auth`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '用户权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users_base
-- ----------------------------
CREATE TABLE `users_base`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `email` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '用户邮箱',
  `name` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '用户名',
  `password` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '用户密码',
  `updated_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '修改时间',
  `created_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE,
  INDEX `login_index`(`email`, `password`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '用户基础信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users_detail
-- ----------------------------
CREATE TABLE `users_detail`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `sex` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '性别(英文)',
  `birthday` date NOT NULL COMMENT '用户生日',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '用户详细信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wuan_fruit
-- ----------------------------
CREATE TABLE `wuan_fruit`  (
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `value` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '午安果',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '午安果' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wuan_fruit_log
-- ----------------------------
CREATE TABLE `wuan_fruit_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scene` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '场景：0未知，1基础，2签到',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `value` int(10) UNSIGNED NOT NULL COMMENT '午安果数量',
  `created_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '午安果日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wuan_points
-- ----------------------------
CREATE TABLE `wuan_points`  (
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `points` float unsigned NOT NULL COMMENT '午安积分',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_bin COMMENT = '午安积分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wuan_sign
-- ----------------------------
CREATE TABLE `wuan_sign`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `value` int(10) UNSIGNED NOT NULL COMMENT '本次签到赠送的午安果数量',
  `created_at` timestamp(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 130029 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '签到记录表' ROW_FORMAT = Dynamic;

