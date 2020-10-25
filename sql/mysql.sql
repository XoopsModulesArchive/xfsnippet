CREATE TABLE xf_snippet (
    snippet_id  INT(11)          NOT NULL AUTO_INCREMENT,
    created_by  INT(11)          NOT NULL DEFAULT '0',
    name        TEXT,
    description TEXT,
    TYPE        INT(11)          NOT NULL DEFAULT '0',
    language    INT(11)          NOT NULL DEFAULT '0',
    license     TEXT             NOT NULL,
    category    INT(11)          NOT NULL DEFAULT '0',
    hits        INT(11) UNSIGNED NOT NULL DEFAULT '0',
    rating      DOUBLE(6, 4)     NOT NULL DEFAULT '0.0000',
    votes       INT(11) UNSIGNED NOT NULL DEFAULT '0',
    comments    INT(11) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (snippet_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_category`
#
# Creation: Sep 12, 2003 at 01:47 PM
# Last update: Sep 12, 2003 at 02:15 PM
#

CREATE TABLE xf_snippet_category (
    type_id INT(11) NOT NULL AUTO_INCREMENT,
    name    TEXT,
    PRIMARY KEY (type_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_language`
#
# Creation: Sep 12, 2003 at 01:53 PM
# Last update: Sep 12, 2003 at 01:59 PM
#

CREATE TABLE xf_snippet_language (
    type_id INT(11) NOT NULL AUTO_INCREMENT,
    name    TEXT,
    PRIMARY KEY (type_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_package`
#
# Creation: Sep 12, 2003 at 02:00 PM
# Last update: Sep 12, 2003 at 02:13 PM
#

CREATE TABLE xf_snippet_package (
    snippet_package_id INT(11) NOT NULL AUTO_INCREMENT,
    created_by         INT(11) NOT NULL DEFAULT '0',
    name               TEXT,
    description        TEXT,
    category           INT(11) NOT NULL DEFAULT '0',
    language           INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (snippet_package_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_package_item`
#
# Creation: Sep 12, 2003 at 02:00 PM
# Last update: Sep 12, 2003 at 02:00 PM
#

CREATE TABLE xf_snippet_package_item (
    snippet_package_item_id    INT(11) NOT NULL AUTO_INCREMENT,
    snippet_package_version_id INT(11) NOT NULL DEFAULT '0',
    snippet_version_id         INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (snippet_package_item_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_package_version`
#
# Creation: Sep 12, 2003 at 02:04 PM
# Last update: Sep 12, 2003 at 02:13 PM
#

CREATE TABLE xf_snippet_package_version (
    snippet_package_version_id INT(11) NOT NULL AUTO_INCREMENT,
    snippet_package_id         INT(11) NOT NULL DEFAULT '0',
    changes                    TEXT,
    version                    TEXT,
    submitted_by               INT(11) NOT NULL DEFAULT '0',
    date                       INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (snippet_package_version_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_type`
#
# Creation: Sep 12, 2003 at 02:01 PM
# Last update: Sep 12, 2003 at 02:03 PM
#

CREATE TABLE xf_snippet_type (
    type_id INT(11) NOT NULL AUTO_INCREMENT,
    name    TEXT,
    PRIMARY KEY (type_id)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `xf_snippet_version`
#
# Creation: Sep 12, 2003 at 02:04 PM
# Last update: Sep 12, 2003 at 02:10 PM
#

CREATE TABLE xf_snippet_version (
    snippet_version_id INT(11) NOT NULL AUTO_INCREMENT,
    snippet_id         INT(11) NOT NULL DEFAULT '0',
    changes            TEXT,
    version            TEXT,
    submitted_by       INT(11) NOT NULL DEFAULT '0',
    date               INT(11) NOT NULL DEFAULT '0',
    code               TEXT,
    PRIMARY KEY (snippet_version_id)
)
    ENGINE = ISAM;

CREATE TABLE xf_snippet_votedata (
    ratingid        INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    lid             INT(11) UNSIGNED    NOT NULL DEFAULT '0',
    ratinguser      INT(11)             NOT NULL DEFAULT '0',
    rating          TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
    ratinghostname  VARCHAR(60)         NOT NULL DEFAULT '',
    ratingtimestamp INT(10)             NOT NULL DEFAULT '0',
    PRIMARY KEY (ratingid),
    KEY ratinguser (ratinguser),
    KEY ratinghostname (ratinghostname),
    KEY lid (lid)
)
    ENGINE = ISAM;



INSERT INTO xf_snippet_category
VALUES (100, 'Choose One');
INSERT INTO xf_snippet_category
VALUES (101, 'UNIX Admin');
INSERT INTO xf_snippet_category
VALUES (102, 'HTML Manipulation');
INSERT INTO xf_snippet_category
VALUES (103, 'BBS Systems');
INSERT INTO xf_snippet_category
VALUES (104, 'Auctions');
INSERT INTO xf_snippet_category
VALUES (105, 'Calendars');
INSERT INTO xf_snippet_category
VALUES (106, 'Database Manipulation');
INSERT INTO xf_snippet_category
VALUES (107, 'Searching');
INSERT INTO xf_snippet_category
VALUES (108, 'File Management');
INSERT INTO xf_snippet_category
VALUES (109, 'Games');
INSERT INTO xf_snippet_category
VALUES (110, 'Voting');
INSERT INTO xf_snippet_category
VALUES (111, 'Shopping Carts');
INSERT INTO xf_snippet_category
VALUES (112, 'Other');
INSERT INTO xf_snippet_category
VALUES (113, 'Math Functions');

INSERT INTO xf_snippet_language
VALUES (100, 'Choose One');
INSERT INTO xf_snippet_language
VALUES (101, 'Other Language');
INSERT INTO xf_snippet_language
VALUES (102, 'C');
INSERT INTO xf_snippet_language
VALUES (103, 'C++');
INSERT INTO xf_snippet_language
VALUES (104, 'Perl');
INSERT INTO xf_snippet_language
VALUES (105, 'PHP');
INSERT INTO xf_snippet_language
VALUES (106, 'Python');
INSERT INTO xf_snippet_language
VALUES (107, 'Unix Shell');
INSERT INTO xf_snippet_language
VALUES (108, 'Java');
INSERT INTO xf_snippet_language
VALUES (109, 'AppleScript');
INSERT INTO xf_snippet_language
VALUES (110, 'Visual Basic');
INSERT INTO xf_snippet_language
VALUES (111, 'TCL');
INSERT INTO xf_snippet_language
VALUES (112, 'Lisp');
INSERT INTO xf_snippet_language
VALUES (113, 'Mixed');
INSERT INTO xf_snippet_language
VALUES (114, 'Javascript');
INSERT INTO xf_snippet_language
VALUES (115, 'SQL');
INSERT INTO xf_snippet_language
VALUES (116, 'C#');

INSERT INTO xf_snippet_type
VALUES (100, 'Choose One');
INSERT INTO xf_snippet_type
VALUES (101, 'Hack');
INSERT INTO xf_snippet_type
VALUES (102, 'Function');
INSERT INTO xf_snippet_type
VALUES (103, 'Full Script');
INSERT INTO xf_snippet_type
VALUES (104, 'Sample Code (HOWTO)');
INSERT INTO xf_snippet_type
VALUES (105, 'README');
INSERT INTO xf_snippet_type
VALUES (106, 'Class');
INSERT INTO xf_snippet_type
VALUES (107, 'Core Hack');











