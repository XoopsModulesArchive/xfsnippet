<?php
// comment callback functions

function xfsnippet_com_update($snip_id, $total_num)
{
    $db = XoopsDatabaseFactory::getDatabaseConnection();

    $sql = 'UPDATE ' . $db->prefix('xf_snippet') . ' SET comments = ' . $total_num . ' WHERE snippet_id = ' . $snip_id;

    $db->query($sql);
}

function xfsnippet_com_approve(&$comment)
{
    // notification mail here
}
