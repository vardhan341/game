Create database game;

CREATE TABLE `user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` varchar(255) NOT NULL,
    `user_name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `avatar` varchar(255) NOT NULL,
    `record_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `updated_on` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET = latin1;

CREATE TABLE `games_list` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `match_id` varchar(255) DEFAULT NULL,
    `user_id` varchar(255) DEFAULT NULL,
    `status` varchar(50) DEFAULT NULL,
    `data` json DEFAULT NULL,
    `commentatory` json DEFAULT NULL,
    `start_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `ends_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `record_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `updated_on` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET = latin1;

CREATE TABLE `match_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `match_id` varchar(255) DEFAULT NULL,
    `user_id` varchar(255) DEFAULT NULL,
    `action` varchar(50) DEFAULT NULL,
    `data` json DEFAULT NULL,
    `record_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `updated_on` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET = latin1;

CREATE TABLE `token` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `token` varchar(255) DEFAULT NULL,
    `user_id` varchar(255) DEFAULT NULL,
    `data` json DEFAULT NULL,
    `record_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `updated_on` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET = latin1;

INSERT INTO `user` VALUES ('USER5f82c8dbcedca','user1','user1@gmail.com','f5516d0c6d9528e804ee2b5e973f1890','User1','',CURRENT_TIMESTAMP,NULL);

INSERT INTO `user` VALUES ('USER5f82c8dbcedab','user2','user2@gmail.com','f5516d0c6d9528e804ee2b5e973f1890','User2','',CURRENT_TIMESTAMP,NULL);

INSERT INTO `user` VALUES ('USER5f82c8dbcedac','user3','user3@gmail.com','f5516d0c6d9528e804ee2b5e973f1890','User3','',CURRENT_TIMESTAMP,NULL);

INSERT INTO `user` VALUES ('USER5f82c8dbcedad','user4','user4@gmail.com','f5516d0c6d9528e804ee2b5e973f1890','User4','',CURRENT_TIMESTAMP,NULL);