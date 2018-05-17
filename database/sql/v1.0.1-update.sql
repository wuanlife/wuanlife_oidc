-- 找回密码表
CREATE TABLE IF NOT EXISTS reset_password
(
  user_id INT UNSIGNED NOT NULL
  COMMENT '用户id',
  token   VARCHAR(255) COLLATE utf8_bin COMMENT '验证token',
  exp     TIMESTAMP    NOT NULL
  COMMENT '过期时间',
  PRIMARY KEY (user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '找回密码表';

-- 午安积分表
CREATE TABLE IF NOT EXISTS wuan_points
(
  user_id INT UNSIGNED NOT NULL
  COMMENT '用户id',
  points INT UNSIGNED NOT NULL DEFAULT 0
  COMMENT '午安积分',
  PRIMARY KEY (user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '午安积分表';

  -- 积分兑换记录表
CREATE TABLE points_order
(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL
  COMMENT '用户id',
  points_alert  INT NOT NULL
  COMMENT '午安影视积分',
  created_at TIMESTAMP,
  PRIMARY KEY (id)
)
