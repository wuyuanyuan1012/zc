/**
 * Database schema required by ZcDbRbac.
 *
 */
drop table if exists `zc_permission`;
drop table if exists `zc_role_to_permission`;
drop table if exists `zc_role`;
drop table if exists `zc_role_hierarchy`;
drop table if exists `zc_user_to_role`;

CREATE TABLE `zc_permission` (
  `zc_permission_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增长主键',
  `name` varchar(128) NOT NULL COMMENT '权限业务主键',
  `desc` varchar(1024) DEFAULT NULL COMMENT '权限描述',
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`zc_permission_id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB COMMENT='权限表';

CREATE TABLE `zc_role_to_permission` (
  `zc_role_id` int(11) NOT NULL,
  `zc_permission_id` int(11) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`zc_role_id`,`zc_permission_id`)
) ENGINE=InnoDB COMMENT='角色权限关系表';

CREATE TABLE `zc_role` (
  `zc_role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色的自增长主键',
  `name` varchar(128) NOT NULL COMMENT '角色的业务主键',
  `desc` varchar(1024) DEFAULT NULL COMMENT '角色描述',
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`zc_role_id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='角色表';

CREATE TABLE `zc_role_hierarchy` (
  `zc_role_id` int(11) NOT NULL,
  `zc_sub_role_id` int(11) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`zc_role_id`,`zc_sub_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色层次表，定义角色之间的层次关系';

CREATE TABLE `zc_user_to_role` (
  `zc_user_id` int(11) NOT NULL,
  `zc_role_id` int(11) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`zc_user_id`,`zc_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户角色关系表';
