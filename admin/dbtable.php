<?php

/**
 * Module to render generic HTML tables for Site Admin
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: dbtable.php,v 1.2 2003/12/09 15:03:38 devsupaul Exp $
 */
require_once '../include/snippet.php';

/**
 *    admin_table_add() - present a form for adding a record to the specified table
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param mixed $baseurl
 */
function admin_table_add($table, $unit, $primary_key, $baseurl)
{
    global $xoopsDB;

    // This query may return no rows, but the field names are needed.

    $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix($table) . ' WHERE ' . $primary_key . '=0');

    if ($result) {
        $cols = unofficial_getNumFields($result);

        echo 'Create a new ' . $unit . ' below:' . "<FORM NAME='add' ACTION='" . $baseurl . "&func=postadd' METHOD='POST'>" . '<TABLE>';

        for ($i = 0; $i < $cols; $i++) {
            $fieldname = unofficial_getFieldName($result, $i);

            echo '<TR><TD><B>' . $fieldname . '</B></TD>' . "<TD><INPUT TYPE='text' NAME='" . $fieldname . "' VALUE=''></TD></TR>";
        }

        echo '</TABLE>' . "<INPUT TYPE='submit' VALUE='Submit New " . ucwords($unit) . "'></FORM>" . myTextForm($baseurl, 'Cancel');
    } else {
        echo $xoopsDB->error();
    }
}

/**
 *    admin_table_postadd() - update the database based on a submitted change
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param mixed $baseurl
 */
function admin_table_postadd($table, $unit, $primary_key, $baseurl)
{
    global $_POST, $xoopsDB;

    $ts = MyTextSanitizer::getInstance();

    $sql = 'INSERT INTO ' . $xoopsDB->prefix($table) . ' (' . implode(',', array_keys($_POST)) . ") VALUES ('" . $ts->addSlashes(implode("','", array_values($_POST))) . "')";

    if ($xoopsDB->queryF($sql)) {
        echo ucfirst($unit) . ' successfully added.';
    } else {
        echo $xoopsDB->error();
    }
}

/**
 *    admin_table_confirmdelete() - present a form to confirm requested record deletion
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param $id          - the id of the record to act on
 * @param mixed $baseurl
 */
function admin_table_confirmdelete($table, $unit, $primary_key, $id, $baseurl)
{
    global $xoopsDB;

    $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix($table) . " WHERE $primary_key=$id");

    if ($result) {
        $cols = unofficial_getNumFields($result);

        echo 'Are you sure you want to delete this ' . $unit . '?' . '<UL>';

        for ($i = 0; $i < $cols; $i++) {
            echo '<LI><B>' . unofficial_getFieldName($result, $i) . '</B> ' . unofficial_getDBResult($result, 0, $i) . '</LI>';
        }

        echo '</UL>' . myTextForm($baseurl . '&func=delete&id=' . $id, 'Delete') . myTextForm($baseurl, 'Cancel');
    } else {
        echo $xoopsDB->error();
    }
}

/**
 *    admin_table_delete() - delete a record from the database after confirmation
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param $id          - the id of the record to act on
 * @param mixed $baseurl
 */
function admin_table_delete($table, $unit, $primary_key, $id, $baseurl)
{
    global $xoopsDB;

    if ($xoopsDB->queryF('DELETE FROM ' . $xoopsDB->prefix($table) . " WHERE $primary_key=$id")) {
        echo ucfirst($unit) . ' successfully deleted.';
    } else {
        echo $xoopsDB->error();
    }
}

/**
 *    admin_table_edit() - present a form for editing a record in the specified table
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param $id          - the id of the record to act on
 * @param mixed $baseurl
 */
function admin_table_edit($table, $unit, $primary_key, $id, $baseurl)
{
    global $xoopsDB;

    $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix($table) . " WHERE $primary_key=$id");

    if ($result) {
        $cols = unofficial_getNumFields($result);

        echo 'Modify the ' . $unit . ' below:' . "<FORM NAME='edit' ACTION='" . $baseurl . '&func=postedit&id=' . $id . "' METHOD='POST'>" . '<TABLE>';

        for ($i = 0; $i < $cols; $i++) {
            $fieldname = unofficial_getFieldName($result, $i);

            $value = unofficial_getDBResult($result, 0, $i);

            echo '<TR><TD><B>' . $fieldname . '</B></TD>';

            if ($fieldname == $primary_key) {
                echo '<TD>' . $value . '</TD></TR>';
            } else {
                echo "<TD><INPUT TYPE='text' NAME='" . $fieldname . "' VALUE='" . $value . "'></TD></TR>";
            }
        }

        echo "</TABLE><INPUT TYPE='submit' VALUE='Submit Changes'></FORM>" . myTextForm($baseurl, 'Cancel');
    } else {
        echo $xoopsDB->error();
    }
}

/**
 *    admin_table_postedit() - update the database to reflect submitted modifications to a record
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param $id          - the id of the record to act on
 * @param mixed $baseurl
 */
function admin_table_postedit($table, $unit, $primary_key, $id, $baseurl)
{
    global $_POST, $xoopsDB;

    $ts = MyTextSanitizer::getInstance();

    $sql = 'UPDATE ' . $xoopsDB->prefix($table) . ' SET ';

    while (list($var, $val) = each($_POST)) {
        if ($var != $primary_key) {
            $sql .= "$var='" . $ts->addSlashes($val) . "', ";
        }
    }

    $sql = preg_replace(', $', ' ', $sql);

    $sql .= "WHERE $primary_key=$id";

    if ($xoopsDB->queryF($sql)) {
        echo ucfirst($unit) . ' successfully modified.';
    } else {
        echo $xoopsDB->error();
    }
}

/**
 *    admin_table_show() - display the specified table, sorted by the primary key, with links to add, edit, and delete
 *
 * @param $table       - the table to act on
 * @param $unit        - the name of the "units" described by the table's records
 * @param $primary_key - the primary key of the table
 * @param mixed $baseurl
 */
function admin_table_show($table, $unit, $primary_key, $baseurl)
{
    global $xoopsDB;

    $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix($table) . " ORDER BY $primary_key");

    if ($result) {
        $rows = $xoopsDB->getRowsNum($result);

        $cols = unofficial_getNumFields($result);

        echo "<TABLE BORDER='0' WIDTH='100%'>" . '<TR>' . "<TD COLSPAN='" . ($cols + 1) . "'><B><FONT>" . ucwords($unit) . 's</FONT></B>' . "[ <A HREF='" . $baseurl . "&func=add'>Add New</A> ]</TD></TR>" . "<TR><TD WIDTH='15%'></TD>";

        for ($i = 0; $i < $cols; $i++) {
            echo '<TD><B>' . unofficial_getFieldName($result, $i) . '</B></TD>';
        }

        echo '</TR>';

        for ($j = 0; $j < $rows; $j++) {
            echo "<TR class='" . (0 != $j % 2 ? 'bg2' : 'bg3') . "'>";

            $id = unofficial_getDBResult($result, $j, 0);

            echo "<TD>[ <A HREF='" . $baseurl . '&func=edit&id=' . $id . "'>edit</A> ] " . "[ <A HREF='" . $baseurl . '&func=confirmdelete&id=' . $id . "'>delete</A> ] </TD>";

            for ($i = 0; $i < $cols; $i++) {
                echo '<TD>' . unofficial_getDBResult($result, $j, $i) . '</TD>';
            }

            echo '</TR>';
        }

        echo '</TABLE>';
    } else {
        echo $xoopsDB->error();
    }
}
