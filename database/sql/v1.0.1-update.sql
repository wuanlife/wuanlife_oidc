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
CREATE TABLE IF NOT EXISTS wuan_score
(
  user_id INT UNSIGNED NOT NULL
  COMMENT '用户id',
  score INT UNSIGNED NOT NULL DEFAULT 0
  COMMENT '午安积分',
  PRIMARY KEY (user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '午安积分表';