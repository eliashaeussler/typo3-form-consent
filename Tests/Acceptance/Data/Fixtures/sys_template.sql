DELETE FROM `sys_template`;

INSERT INTO `sys_template` (`uid`, `pid`, `deleted`, `hidden`, `title`, `root`, `clear`, `include_static_file`, `constants`, `config`)
VALUES (1, 1, 0, 0, 'Root', 1, 3, 'EXT:fluid_styled_content/Configuration/TypoScript/,EXT:form/Configuration/TypoScript/,EXT:form_consent/Configuration/TypoScript', '', 'page = PAGE\npage.10 < styles.content.get');
