DELETE FROM `pages`;

INSERT INTO `pages` (`uid`, `pid`, `deleted`, `hidden`, `title`, `slug`, `doktype`, `is_siteroot`)
VALUES (1, 0, 0, 0, 'Home', '/', 1, 1),
			 (2, 1, 0, 0, 'Confirmation', '/confirmation', 1, 0);
