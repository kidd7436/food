Delimiter ;
-- -----------------------------------------------------------------------------
-- 建立資料庫
-- -----------------------------------------------------------------------------
DROP DATABASE IF EXISTS `db_food`;
CREATE DATABASE  `db_food` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `db_food`;

-- -----------------------------------------------------------------------------
-- 開啟可以使用自定的function
-- -----------------------------------------------------------------------------
SET global log_bin_trust_function_creators=TRUE;

-- -----------------------------------------------------------------------------
-- 因應檔案上傳避免過大，所以提高 mysql 的全域設定，需要有 SUPER 權限
-- -----------------------------------------------------------------------------
SET @@global.max_allowed_packet = 4194304;

-- -----------------------------------------------------------------------------
-- 訊息記錄資料表
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `_upgrade_msg`;
CREATE TABLE `_upgrade_msg`
(
    `id` int(11) NOT NULL AUTO_INCREMENT ,
    `msg` text DEFAULT NULL ,
    `dt` int(11) NOT NULL DEFAULT 0 COMMENT '建立日期' ,
    PRIMARY KEY  ( `id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = '_upgrade_msg', `dt` = now();

-- -----------------------------------------------------------------------------
-- 紀錄 Triggers、storeprocedure的異動紀錄
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `_udf_version`;
CREATE TABLE `_udf_version`
(
    `id` int NOT NULL AUTO_INCREMENT ,
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '程序名稱' ,
    `ver` decimal(3,2) NOT NULL DEFAULT 0 COMMENT '版本' ,
    `dt` int(11) NOT NULL DEFAULT 0 COMMENT '建立日期' ,
    PRIMARY KEY  ( `id` ) ,
    UNIQUE ( `name` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = '_udf_version', `dt` = now();

-- -----------------------------------------------------------------------------
--  最新消息
-- -----------------------------------------------------------------------------
CREATE TABLE `news` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) DEFAULT NULL COMMENT '標題',
  `content` text DEFAULT NULL COMMENT '内容',
  `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態開關(0停用1啟用)' ,
  `dts` int(11) DEFAULT NULL COMMENT '開始顯示時間.設0表示不指定',
  `dte` int(11) DEFAULT NULL COMMENT '結束顯示時間.設0表示不指定',
  `sort` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '排序',
  `updatedt` INT(11) DEFAULT 0 COMMENT '更新時間',
  `updateid` INT(11) DEFAULT 0 COMMENT '異動人員',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- for degug
insert into `_upgrade_msg` set `msg` = 'news', `dt` = now();

-- -----------------------------------------------------------------------------
-- 使用者相關資料表
-- -----------------------------------------------------------------------------
CREATE TABLE `k_user`
(
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '帳號編號' ,
    `account` varchar(20) NOT NULL DEFAULT '' COMMENT '帳號' ,
    `pass` varchar(32) NOT NULL DEFAULT '' COMMENT '密碼' ,
    `name` varchar(20) NOT NULL DEFAULT '' COMMENT '名稱' ,
    `acl` int(1) NOT NULL DEFAULT 1 COMMENT '階層' ,
    `phone` VARCHAR(20) DEFAULT NULL COMMENT '電話號碼',
    `email` VARCHAR(100) DEFAULT NULL COMMENT '電子信箱',
    `address` VARCHAR(100) DEFAULT NULL COMMENT '地址',
    `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態開關(0停用1啟用)' ,
    `disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '禁止登入' ,
    `pow` int(11) DEFAULT 0 COMMENT '權限值' ,
    `logcount` int(11) NOT NULL DEFAULT 0 COMMENT '登入次數' ,
    `failcount` int(1) NOT NULL DEFAULT 0 COMMENT '登入失敗次數' ,
    `lastlogdt` int(11) DEFAULT NULL COMMENT '最後登入時間' ,
    `lastlogip` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最後登入IP' ,
    `createdt` int(11) NOT NULL DEFAULT 0 COMMENT '創建時間' ,
    `createAccount` varchar(20) DEFAULT NULL COMMENT '創建人員帳號' ,
    `chgpwdt` int(11) NOT NULL DEFAULT 0 COMMENT '密碼更新時間' ,
    `updateid` int(11) NOT NULL DEFAULT 0 COMMENT '異動人員' ,
    `updatedt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間' ,
    `updateip` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '異動人員IP' ,
    PRIMARY KEY ( `id` ) ,
    UNIQUE KEY `account` ( `account` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'k_user', `dt` = now();

-- -----------------------------------------------------------------------------
-- 測試用資料
-- -----------------------------------------------------------------------------
insert into `k_user` (`id` , `account` , `pass` , `name` , `pow` ) values
 ('1',  'Admin', md5(  'aaa111' ) ,  'ADMIN' , 4095 );

-- -----------------------------------------------------------------------------
-- SESSION 儲存資料表
-- -----------------------------------------------------------------------------
CREATE TABLE `k_sessions`
(
    `sid` varchar(32) NOT NULL ,
    `uid` int(11) default NULL ,
    `createtime` int(11) NOT NULL default 0 ,
    `lastupdate` int(11) NOT NULL default 0 ,
    `val` varchar(255) default NULL ,
    `host` int( 11 ) UNSIGNED NOT NULL DEFAULT 0 ,
    `ip` int( 11 ) UNSIGNED NOT NULL DEFAULT 0 ,
    `proxyip` int( 11 ) UNSIGNED DEFAULT 0 ,
    `agent` varchar(255) default NULL ,
    `uri` varchar(160) NOT NULL default '' ,
    PRIMARY KEY  ( `sid` ) ,
    KEY `ip` ( `ip` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'k_sessions', `dt` = now();

-- -----------------------------------------------------------------------------
-- 使用者登入相關資料表 LOG表
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `k_log`;
CREATE TABLE `k_log`
(
    `user_id` int(11) NOT NULL default 0 ,
    `server` varchar(50) NOT NULL default '' ,
    `ip` varchar(16) NOT NULL default '' ,
    `proxyip` varchar(16) NOT NULL default '' ,
    `logindt` int(11) NOT NULL default 0 ,
    `logoutdt` int(11) default NULL ,
    `agent` varchar(255) default NULL ,
    `beginip` varchar(16) default NULL ,
    `endip` varchar(16) default NULL ,
    `country` varchar(20) default NULL ,
    `area` varchar(50) default NULL ,
    KEY `ip` ( `ip` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'k_log', `dt` = now();

-- -----------------------------------------------------------------------------
-- 開關設定，更新時,要儲存異動記錄
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `k_config`;
CREATE TABLE `k_config`
(
    `id` varchar(50) NOT NULL default '' ,
    `val` varchar(200) NOT NULL ,
    `remarks` varchar(50) default NULL ,
    `updateid` int(11) NOT NULL default 0 ,
    `updateip` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '異動人員IP' ,
    `updatedt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間' ,
    PRIMARY KEY  ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'k_config', `dt` = now();

-- -----------------------------------------------------------------------------
-- 基本資料
-- -----------------------------------------------------------------------------
INSERT INTO `k_config` (`id`, `val`, `remarks`)
VALUES
-- 系統本身相關
('systemcheck','0','系统维护中'),
('login_fail_count','25','登入密码容错次数'),
('shipping_free','0','免運設定'),
('shipping','25','運費設定'),
('version','v1.00','版本');

-- -----------------------------------------------------------------------------
-- 資料表格式： `phplogs`
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `phplogs`;
CREATE TABLE `phplogs`
(
    `id` int(11) NOT NULL auto_increment ,
    `errno` int(2) NOT NULL ,
    `errtype` varchar(32) NOT NULL ,
    `errstr` text NOT NULL ,
    `errfile` varchar(255) NOT NULL ,
    `errline` int(4) NOT NULL ,
    `time` datetime NOT NULL ,
    `uid` int(11) NOT NULL COMMENT '造成錯誤的使用者' ,
    PRIMARY KEY  ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'phplogs', `dt` = now();

-- -----------------------------------------------------------------------------
-- 商品相關資料表
-- -----------------------------------------------------------------------------
CREATE TABLE `product`
(
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '編號' ,
    `title` varchar(20) NOT NULL DEFAULT '' COMMENT '商品名稱' ,
    `money` int(11) NOT NULL DEFAULT 0 COMMENT '售價' ,
    `discount` int(11) NOT NULL DEFAULT 0 COMMENT '折扣價' ,
    `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '類型(1便當2禮盒3即食)' ,
    `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態開關(0停用1啟用)' ,
    `note` varchar(255) NOT NULL DEFAULT '' COMMENT '備註' ,
    `peddledt` varchar(20) NOT NULL DEFAULT '' COMMENT '販賣時間' ,
    `content` text NOT NULL COMMENT '内容',
    `sort` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '排序',
    `updateid` int(11) NOT NULL DEFAULT 0 COMMENT '異動人員' ,
    `updatedt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間' ,
    `updateip` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '異動人員IP' ,
    PRIMARY KEY ( `id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'Product', `dt` = now();

-- -----------------------------------------------------------------------------
-- 資料
-- -----------------------------------------------------------------------------
insert into `product` (`id`, `title`, `money`, `discount`, `type`, `content`, `sort` ) values
-- -----------------------------------------------------------------------------
-- 便當
-- -----------------------------------------------------------------------------
( 1, '招牌餐盒', 85, 85, 1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配滿滿的招牌大塊豬肉片與份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 1 ),
( 2, '蒜泥白肉餐盒', 85, 85, 1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配滿滿的蒜泥白肉與份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 2 ),
( 3, '三杯雞餐盒', 95, 95,  1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配滿滿的三杯雞與份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 3 ),
( 4, '嫩煎雞腿排餐盒', 90, 90, 1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配低油香煎的雞腿排與份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 4 ),
( 5, '宮保雞丁餐盒', 95, 95, 1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配滿滿的樂即食人氣餐點-宮保雞丁，以及份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 5 ),
( 6, '嫩煎虱目魚肚餐盒', 90, 90, 1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配大塊的嫩煎虱目魚肚與份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 6 ),
( 7, '嫩煎豬排餐盒', 80, 80, 1, '少油少鹽的料理方式，保留了季節時蔬的自然鮮甜，再搭配滿滿的招牌大塊的嫩煎豬排與份量剛剛好的紫米飯，輕鬆享受少油、少鹽的餐盒，每一口就是這樣簡單、健康又無負擔。', 7 ),
-- -----------------------------------------------------------------------------
-- 禮盒
-- -----------------------------------------------------------------------------
( 8, '喜稅禮盒', 160, 160, 2, '', 1 ),
( 9, '黃金滿屋禮盒', 220, 220, 2, '', 2 ),
( 10, '喜氣洋洋禮盒', 180, 180, 2, '', 3 ),
( 11, '福氣滿溢禮盒', 240, 240, 2, '', 4 ),
( 12, '如意素油飯I', 160, 160, 2, '', 5 ),
( 13, '如意素油飯II', 180, 180, 2, '', 6 ),
( 14, '提籃油飯3斤', 360, 360, 2, '', 7 ),
( 15, '提籃油飯5斤', 600, 600, 2, '', 8 ),
( 16, '林記辣丁香', 180, 180, 2, '', 9 ),
( 17, '嫁娶米糕', 80, 80, 2, '', 10 ),
( 18, '延祥麻油雞', 130, 130, 2, '', 11 ),
( 19, '延祥麻油雞(全雞)', 850, 850, 2, '', 12 ),
-- -----------------------------------------------------------------------------
-- 即食
-- -----------------------------------------------------------------------------
( 20, '魚皮海鮮羹', 499, 300, 3, '', 1 ),
( 21, '古早味油飯', 70, 70, 3, '', 2 ),
( 22, '蜜汁排骨', 120, 89, 3, '', 3 ),
( 23, '古早味爌肉', 140, 99, 3, '', 4 ),
( 24, '宮保雞丁', 120, 120, 3, '', 5 ),
( 25, '椒鹽毛豆', 120, 79, 3, '', 6 ),
( 26, '辣味小魚乾', 210, 210, 3, '', 7 ),
( 27, '古早味香菇雞湯', 160, 160, 3, '', 8 ),
( 28, '古早味麻油雞', 160, 160, 3, '', 9 ),
( 29, '恏食豚骨湯(麵)', 130, 99, 3, '', 10 ),
( 30, '古早味芋粿', 170, 170, 3, '', 11 ),
( 31, '蜜汁全雞', 499, 499, 3, '', 12 ),
( 32, '紹興醉蝦', 499, 499, 3, '', 13 );

-- -----------------------------------------------------------------------------
-- 輪播圖管理
-- -----------------------------------------------------------------------------
CREATE TABLE `carousel`
(
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '編號' ,
    `kinds` VARCHAR(4) NOT NULL COMMENT 'TT輪播圖FP浮動圖AB關於QAQ&A' ,
    `title` varchar(20) NOT NULL DEFAULT '' COMMENT '標題' ,
    `link` VARCHAR(255) DEFAULT NULL COMMENT '連結網址',
    `content` text DEFAULT NULL COMMENT '内容',
    `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態開關(0停用1啟用)' ,
    `sort` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '排序',
    `updateid` int(11) NOT NULL DEFAULT 0 COMMENT '異動人員' ,
    `updatedt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '異動時間' ,
    `updateip` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '異動人員IP' ,
    PRIMARY KEY ( `id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- for debug
insert into `_upgrade_msg` set `msg` = 'carousel', `dt` = now();

-- -----------------------------------------------------------------------------
-- 訂單管理
-- -----------------------------------------------------------------------------
CREATE TABLE `orderdata` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `user_id` INT(11) NOT NULL COMMENT '會員ID',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '訂購者姓名' ,
  `order_id` BIGINT(18) NOT NULL COMMENT '訂單號編碼', -- 1表示一般汇款+YMDHIS+毫秒未4码
  `typeid` TINYINT(1) NOT NULL DEFAULT -1 COMMENT '入款方式1轉帳2LINEPAY', -- 1轉帳2LINEPAY
  `takeid` TINYINT(1) NOT NULL DEFAULT -1 COMMENT '取貨方式1宅配2自取', -- 1宅配2自取
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '電話號碼',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '電子信箱',
  `address` VARCHAR(100) DEFAULT NULL COMMENT '地址',
  `content` text DEFAULT NULL COMMENT '内容',
  `savetime` INT(11) NOT NULL COMMENT '入款時間',
  `createtime` INT(11) NOT NULL COMMENT '產生時間',
  `updatetime` INT(11) NOT NULL COMMENT '更新時間',
  `checktime` INT(11) NOT NULL COMMENT '確認時間',
  `updateid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作者',
  `amount` decimal(11,3) NOT NULL COMMENT '總金額',
  `status` INT(1) NOT NULL COMMENT '狀態', -- 0無動作1已完成2取消3處理中4失敗
  `note` VARCHAR(100) NOT NULL COMMENT '備註',
  PRIMARY KEY  (`id`),
  INDEX (`user_id`),
  INDEX (`typeid`),
  INDEX (`createtime`),
  UNIQUE (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- for degug
insert into `_upgrade_msg` set `msg` = 'orderdata', `dt` = now();

-- -----------------------------------------------------------------------------
-- 完成資料表異動之後，紀錄
-- -----------------------------------------------------------------------------
-- for debug
insert into `_upgrade_msg` set `msg` = '資料表異動完成, 以下處理程序部份 ', `dt` = now();

-- for debug
insert into `_upgrade_msg` set `msg` = '資料庫完成,共1筆記錄', `dt` = now();