This needs a table in a database called evesupport, that has a list of multiple eve databases in it. 

REATE TABLE `dbversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
)

insert into dbversions(0,"eve","current");


It /needs/ to be ID 0. Eve is the name of the database. Current is the display name.

You may need to add indexes to speed this up.
