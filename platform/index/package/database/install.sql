SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ai_index_category
-- ----------------------------
DROP TABLE IF EXISTS `ai_index_category`;
CREATE TABLE `ai_index_category`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apps_id` int(11) NULL DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `alias_title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `sort` int(11) NULL DEFAULT 0,
  `is_show` tinyint(2) NULL DEFAULT 1,
  `update_time` int(11) NULL DEFAULT NULL,
  `create_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `ID`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品目录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for ai_index_app
-- ----------------------------
DROP TABLE IF EXISTS `ai_index_app`;
CREATE TABLE `ai_index_app`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NULL DEFAULT NULL,
  `cate_id` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `create_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `ID`(`id`) USING BTREE,
  INDEX `PARENT_ID`(`cate_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品目录' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
