<?php

require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xf/include/db.php';
require_once XOOPS_ROOT_PATH . '/modules/xf/include/mysql.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/vars.php';

if ($xoopsUser) {
    if ('frompackage' == $type && $snippet_version_id && $snippet_package_version_id) {
        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_package_version') . " WHERE submitted_by='" . $xoopsUser->getVar('uid') . "'" . " AND snippet_package_version_id='$snippet_package_version_id'";

        $result = $xoopsDB->query($sql);

        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&id=' . $snippet_package_version_id, 2, _XF_SNP_ONLYCREATORCANDELETEFROMPACKAGE);
        } else {
            $sql = 'DELETE FROM ' . $xoopsDB->prefix('xf_snippet_package_item') . " WHERE snippet_version_id='$snippet_version_id'" . " AND snippet_package_version_id='$snippet_package_version_id'";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&id=' . $snippet_package_version_id, 2, _XF_SNP_SNIPPETINPACKAGEDOESNOTEXIST);
            } else {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/add_snippet_to_package.php?snippet_package_version_id=' . $snippet_package_version_id, 2, _XF_SNP_ITEMREMOVEDFROMPACKAGE);
            }
        }
    } elseif ('snippet' == $type && $snippet_version_id) {
        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_version') . " WHERE snippet_version_id='$snippet_version_id'" . " AND submitted_by='" . $xoopsUser->getVar('uid') . "'";

        $result = $xoopsDB->queryF($sql);

        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=snippet&id=' . $snippet_version_id, 2, _XF_SNP_SNIPPETDOESNOTEXIST);
        } else {
            $snippet_id = unofficial_getDBResult($result, 0, 'snippet_id');

            // do the delete

            $sql = 'DELETE FROM ' . $xoopsDB->prefix('xf_snippet_version') . " WHERE snippet_version_id='$snippet_version_id'" . " AND submitted_by='" . $xoopsUser->getVar('uid') . "'";

            $result = $xoopsDB->queryF($sql);

            // see if any versions of this snippet are left

            $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_version') . " WHERE snippet_id='$snippet_id'";

            $result = $xoopsDB->query($sql);

            if (!$result || $xoopsDB->getRowsNum($result) < 1) {
                // since no version of this snippet exist, delete the main snippet entry,

                // even if this person is not the creator of the original snippet

                $sql = 'DELETE FROM ' . $xoopsDB->prefix('xf_snippet') . " WHERE snippet_id='$snippet_id'";

                $result = $xoopsDB->queryF($sql);
            }

            if ($id != $snippet_version_id) {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=snippet&id=' . $id, 2, _XF_SNP_SNIPPETREMOVED);
            } else {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_SNIPPETREMOVED);
            }
        }
    } elseif ('package' == $type && $snippet_package_version_id) {
        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_package_version') . " WHERE submitted_by='" . $xoopsUser->getVar('uid') . "'" . " AND snippet_package_version_id='$snippet_package_version_id'";

        $result = $xoopsDB->query($sql);

        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&id=' . $snippet_package_version_id, 2, _XF_SNP_ONLYCREATORCANDELETEPACKAGE);
        } else {
            $snippet_package_id = unofficial_getDBResult($result, 0, 'snippet_package_id');

            // do the version delete

            $sql = 'DELETE FROM ' . $xoopsDB->prefix('xf_snippet_package_version') . " WHERE submitted_by='" . $xoopsUser->getVar('uid') . "'" . " AND snippet_package_version_id='$snippet_package_version_id'";

            $result = $xoopsDB->queryF($sql);

            // delete snippet_package_items

            $sql = 'DELETE FROM ' . $xoopsDB->prefix('xf_snippet_package_item') . " WHERE snippet_package_version_id='$snippet_package_version_id'";

            $result = $xoopsDB->queryF($sql);

            // see if any versions of this package remain

            $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_package_version') . " WHERE snippet_package_id='$snippet_package_id'";

            $result = $xoopsDB->query($sql);

            if (!$result || $xoopsDB->getRowsNum($result) < 1) {
                // since no versions of this package remain,

                // delete the main package even if the user didn't create it

                $sql = 'DELETE FROM ' . $xoopsDB->prefix('xf_snippet_package') . " WHERE snippet_package_id='$snippet_package_id'";

                $result = $xoopsDB->queryF($sql);
            }

            redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_PACKAGEREMOVED);
        }
    } else {
        redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_BADURL);
    }
} else {
    redirect_header(XOOPS_URL . '/user.php', 2, _NOPERM);

    exit;
}
