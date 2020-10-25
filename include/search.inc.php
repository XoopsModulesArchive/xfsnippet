<?php

function snippet_search($queryarray, $andor, $limit, $offset, $userid)
{
    global $xoopsDB;

    $sql = 'SELECT  ver.snippet_id, ver.date, ver.submitted_by, snip.name,snip.description' . ' FROM ' . $xoopsDB->prefix('xf_snippet') . ' AS snip' . ', ' . $xoopsDB->prefix('xf_snippet_version') . ' AS ver' . ' WHERE snip.snippet_id=ver.snippet_id';

    if (0 != $userid) {
        $sql .= ' AND ver.submitted_by=' . $userid . ' ';
    }

    // because count() returns 1 even if a supplied variable

    // is not an array, we must check if $querryarray is really an array

    if (is_array($queryarray) && $count = count($queryarray)) {
        $sql .= " AND ((snip.description LIKE '%$queryarray[0]%' OR ver.code LIKE '%$queryarray[0]%')";

        for ($i = 1; $i < $count; $i++) {
            $sql .= " $andor ";

            $sql .= "(snip.description LIKE '%$queryarray[$i]%' OR ver.code LIKE '%$queryarray[$i]%')";
        }

        $sql .= ')';
    }

    $sql .= ' ORDER BY ver.date DESC';

    $result = $xoopsDB->query($sql, $limit, $offset);

    $ret = [];

    $i = 0;

    while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
        $ret[$i]['link'] = 'detail.php?type=snippet&snippet_id=' . $myrow['snippet_id'] . '';

        $ret[$i]['title'] = $myrow['name'];

        $ret[$i]['time'] = $myrow['date'];

        $ret[$i]['uid'] = $myrow['submitted_by'];

        $i++;
    }

    return $ret;
}
