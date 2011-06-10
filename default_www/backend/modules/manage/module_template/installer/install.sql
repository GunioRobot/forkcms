CREATE TABLE IF NOT EXISTS `{$module.name}` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `language` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
{iteration:fieldsLeft}
 `{$fieldsLeft.name}` {$fieldsLeft.sql_type} CHARACTER SET utf8 COLLATE utf8_unicode_ci{option:fieldsLeft.mandatory} NOT NULL{/option:fieldsLeft.mandatory},
{/iteration:fieldsLeft}
{iteration:fieldsRight}
 `{$fieldsRight.name}` {$fieldsRight.sql_type} CHARACTER SET utf8 COLLATE utf8_unicode_ci{option:fieldsRight.mandatory} NOT NULL{/option:fieldsRight.mandatory},
{/iteration:fieldsRight}
 `created_on` datetime NOT NULL,
 `edited_on` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='{$module.description}' AUTO_INCREMENT=1 ;