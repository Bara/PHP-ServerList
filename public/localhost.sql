CREATE TABLE `lastscan` (
  `last` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

INSERT INTO `lastscan` (`last`) VALUES
('0');

CREATE TABLE `servers` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `ip` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `port` smallint(5) UNSIGNED NOT NULL DEFAULT 27015,
  `active` tinyint(1) NOT NULL,
  `data` text CHARACTER SET latin1 DEFAULT NULL,
  `players` text CHARACTER SET latin1 DEFAULT NULL,
  `count` tinyint(1) NOT NULL,
  `display` tinyint(1) NOT NULL,
  `description` varchar(255) CHARACTER SET latin1 NOT NULL,
  `lastscan` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `lastSuccessScan` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `sourceBans` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `gameME` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `queryPort` smallint(5) DEFAULT NULL,
  `ts_slots` smallint(5) DEFAULT NULL,
  `ts_maxSlots` smallint(5) DEFAULT NULL,
  `discordCount` smallint(5) DEFAULT NULL,
  `discordInvite` varchar(255) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

ALTER TABLE `lastscan`
  ADD PRIMARY KEY (`last`) USING BTREE;

ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`) USING BTREE;

ALTER TABLE `servers`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
