SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ai_demo
-- ----------------------------
DROP TABLE IF EXISTS `ai_demo`;
CREATE TABLE `ai_demo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apps_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of ai_demo
-- ----------------------------
SET FOREIGN_KEY_CHECKS=1;
