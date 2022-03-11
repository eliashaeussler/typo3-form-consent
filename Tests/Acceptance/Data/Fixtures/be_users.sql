SET @username := 'admin';
SET @password := '$argon2i$v=19$m=65536,t=16,p=1$UXBGdzN4dTRjNkRDS1FCOQ$l0yX4DO/Zd3wGhvppCeZJeITX/p1dpv36swzyydBoVY';

DELETE
FROM `be_users`
WHERE `username` COLLATE utf8mb4_general_ci = @username;

INSERT INTO `be_users` (`username`, `password`, `admin`)
VALUES (@username, @password, 1);
