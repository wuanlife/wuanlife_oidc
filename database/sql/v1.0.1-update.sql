-- 找回密码表
CREATE TABLE IF NOT EXISTS reset_password
(
  user_id    INT UNSIGNED                        NOT NULL
  COMMENT '用户id',
  token      VARCHAR(255) COLLATE utf8_bin
  COMMENT '验证token',
  created_at TIMESTAMP default CURRENT_TIMESTAMP NOT NULL,
  exp        TIMESTAMP                           NOT NULL
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
  user_id INT UNSIGNED   NOT NULL
  COMMENT '用户id',
  points  FLOAT UNSIGNED NOT NULL DEFAULT 0
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
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      INT UNSIGNED NOT NULL
  COMMENT '用户id',
  points_alert FLOAT        NOT NULL
  COMMENT '午安影视积分',
  created_at   TIMESTAMP,
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '积分兑换记录表';

-- 子应用积分系统详情表
CREATE TABLE app_point_detail
(
  id            INT PRIMARY KEY              NOT NULL
  COMMENT '标识id' AUTO_INCREMENT,
  name          VARCHAR(20) COLLATE utf8_bin NOT NULL UNIQUE
  COMMENT '应用名',
  exchange_rate FLOAT                        NOT NULL
  COMMENT '兑换汇率',
  address       VARCHAR(255)                 NOT NULL
  COMMENT '该应用的地址'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '子应用积分系统详情表';