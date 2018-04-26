-- 用户基础表
CREATE TABLE IF NOT EXISTS users_base
(
  id         INT UNSIGNED AUTO_INCREMENT                            NOT NULL
  COMMENT '用户id',
  email      VARCHAR(30) COLLATE utf8_bin                           NOT NULL
  COMMENT '用户邮箱',
  name       CHAR(20) COLLATE utf8_bin                              NOT NULL
  COMMENT '用户名',
  password   CHAR(32) COLLATE utf8_bin                              NOT NULL
  COMMENT '用户密码',
  updated_at TIMESTAMP default CURRENT_TIMESTAMP                    NOT NULL
  COMMENT '修改时间',
  created_at TIMESTAMP                                              NOT NULL
  COMMENT '注册时间',
  PRIMARY KEY (id),
  KEY login_index(email, password),
  UNIQUE KEY (name)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户基础信息表';

-- 用户头像表
CREATE TABLE IF NOT EXISTS avatar_url
(
  user_id    INT UNSIGNED AUTO_INCREMENT   NOT NULL
  COMMENT '用户id',
  url        VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '图片url',
  delete_flg TINYINT UNSIGNED              NOT NULL DEFAULT 0
  COMMENT '图片是否已删除',
  KEY image_user_id(user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户头像url表';

-- 用户详细信息表
-- 储存登陆用的基本信息，日后可扩展一张详细信息表
CREATE TABLE IF NOT EXISTS users_detail
(
  id       INT UNSIGNED AUTO_INCREMENT  NOT NULL
  COMMENT '用户id',
  sex      VARCHAR(20) COLLATE utf8_bin NOT NULL
  COMMENT '性别(英文)',
  birthday DATE                         NOT NULL
  COMMENT '用户生日',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户详细信息表';

-- 性别对应关系表
-- 储存登陆用的基本信息，日后可扩展一张详细信息表
CREATE TABLE IF NOT EXISTS sex_detail
(
  id  VARCHAR(20) COLLATE utf8_bin NOT NULL
  COMMENT '性别标识(英文)',
  sex VARCHAR(10) COLLATE utf8_bin NOT NULL
  COMMENT '性别类型',
  PRIMARY KEY (id),
  KEY sex_detail_index(sex)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='性别对应关系表';

-- 用户权限表
-- 用于实现管理员功能，
-- 因为取消了group功能，所以身份关系是唯一的，但是考虑到日后的可扩展性，
-- 此处使用 | 操作符来进行状态的叠加，使用 & 运算符进行状态的判断
CREATE TABLE IF NOT EXISTS users_auth
(
  id   INT UNSIGNED              NOT NULL
  COMMENT '用户id',
  name CHAR(20) COLLATE utf8_bin NOT NULL
  COMMENT '用户名',
  auth INT UNSIGNED              NOT NULL
  COMMENT '用户权限',
  PRIMARY KEY (id),
  KEY auth(auth)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户权限表';

-- 权限对应关系表
-- 使用 << 运算符来得到不同的状态码
CREATE TABLE IF NOT EXISTS auth_detail
(
  id        INT UNSIGNED AUTO_INCREMENT  NOT NULL
  COMMENT '权限id(偏移量)',
  indentity VARCHAR(30) COLLATE utf8_bin NOT NULL
  COMMENT '权限类型',
  PRIMARY KEY (id),
  UNIQUE KEY (indentity)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='权限对应关系表';