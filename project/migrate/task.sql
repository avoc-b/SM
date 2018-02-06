

CREATE TABLE task (
  id bigint(20) unsigned NOT NULL identity,
  parent_id bigint(20) unsigned NOT NULL,
  title varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  category bigint unsigned NOT NULL,
  price float NOT NULL,
  year year(4) DEFAULT NULL,
  month tinyint(2) DEFAULT NULL,
  day tinyint(2) DEFAULT NULL,
  start time NOT NULL,
  end time NOT NULL,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY category (category),
  CONSTRAINT task_ibfk_2 FOREIGN KEY (category) REFERENCES task_category (id) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE task_category (
  id bigint NOT NULL identity,
  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);


CREATE TABLE task_description (
  task bigint(20) unsigned NOT NULL,
  text text COLLATE utf8_unicode_ci NOT NULL,
  KEY task (task),
  CONSTRAINT task_description_ibfk_2 FOREIGN KEY (task) REFERENCES task (id) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
