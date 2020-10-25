<?php

require_once '../../mainfile.php';
$GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_detail.html';
require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/html.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/vars.php';

$post_changes = $_GET['post_changes'] ?? 0;
$name = $_GET['name'] ?? 0;
$description = $_GET['description'] ?? 0;
$language = $_GET['language'] ?? 0;
$category = $_GET['category'] ?? 0;

if ($xoopsUser) {
    if ($post_changes) {
        if ($name && $description && 0 != $language && 0 != $category && $version) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xf_snippet_package') . ' (category,created_by,name,description,language)' . " VALUES ('$category','" . $xoopsUser->getVar('uid') . "','" . $ts->addSlashes($name) . "','" . $ts->addSlashes($description) . "','$language')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                // error in database

                require_once XOOPS_ROOT_PATH . '/header.php';

                $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPETPACKAGE));

                $main .= _XF_SNP_PACKAGEINSERTERROR . $xoopsDB->error();

                require XOOPS_ROOT_PATH . '/footer.php';

                exit;
            }  

            $feedback .= _XF_SNP_PACKAGEADDED;

            $snippet_package_id = $xoopsDB->getInsertId();

            $sql = 'INSERT INTO '
                                      . $xoopsDB->prefix('xf_snippet_package_version')
                                      . ' '
                                      . '(snippet_package_id,changes,version,submitted_by,date)'
                                      . " VALUES ('$snippet_package_id','"
                                      . $ts->addSlashes($changes)
                                      . "',"
                                      . "'"
                                      . $ts->addSlashes($version)
                                      . "','"
                                      . $xoopsUser->getVar('uid')
                                      . "','"
                                      . time()
                                      . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                // error in database

                require_once XOOPS_ROOT_PATH . '/header.php';

                $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPETPACKAGE));

                $main .= _XF_SNP_PACKAGEVERSIONBINSERTERROR;

                $main .= $xoopsDB->error();

                require_once XOOPS_ROOT_PATH . '/footer.php';

                exit;
            }  

            // so far so good - now add snippets to the package

            // id for this snippet_package_version

            $snippet_package_version_id = $xoopsDB->getInsertId();

            redirect_header(XOOPS_URL . "/modules/xfsnippet/add_snippet_to_package.php?snippet_package_version_id=$snippet_package_version_id", 0, '');

            exit;
        }  

        addMsg(_XF_SNP_GOBACKFILLALLINFO);
    }

    // sql queries

    $sql_category = $xoopsDB->query('SELECT type_id,name FROM ' . $xoopsDB->prefix('xf_snippet_category') . ' ORDER BY name ASC');

    $SCRIPT_CATEGORY_ids = util_result_column_to_array($sql_category, 0);

    $SCRIPT_CATEGORY_val = util_result_column_to_array($sql_category, 1);

    $SCRIPT_CATEGORY_val[0] = 'Any';

    // sql queries

    $sql_language = $xoopsDB->query('SELECT type_id,name FROM ' . $xoopsDB->prefix('xf_snippet_language') . ' ORDER BY name ASC');

    $SCRIPT_LANGUAGE_ids = util_result_column_to_array($sql_language, 0);

    $SCRIPT_LANGUAGE_val = util_result_column_to_array($sql_language, 1);

    $SCRIPT_LANGUAGE_val[0] = 'Any';

    require_once XOOPS_ROOT_PATH . '/header.php';

    $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPETPACKAGE));

    $main = '
<P>
' . _XF_SNP_CANGROUPTOGETHER . '
<P>
<OL>
 <LI>' . _XF_SNP_CREATEPACKAGETHISFORM . '
 <LI>' . _XF_SNP_THENADDSNIPPETSTOIT . '
</OL>
<P>
<FONT COLOR="RED"><B>' . _XF_SNP_NOTE . ':</B></FONT>
' . _XF_SNP_YOUCANSUBMITPACKAGEBYBROWSE . '
<P>
<FORM ACTION="' . XOOPS_URL . '/modules/xfsnippet/package.php" METHOD="POST">
 <INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
 <INPUT TYPE="HIDDEN" NAME="changes" VALUE="' . _XF_SNP_FIRSTPOSTEDVERSION . '">
 <TABLE>
  <TR>
   <TD COLSPAN="2">
    <B>' . _XF_SNP_TITLE . ':</B>
    <BR>
    <INPUT TYPE="TEXT" NAME="name" VALUE="' . $name . '" SIZE="45" MAXLENGTH="60">
   </TD>
  </TR>
  <TR>
   <TD COLSPAN="2">
    <B>' . _XF_G_DESCRIPTION . ':</B>
    <BR>
    <TEXTAREA NAME="description" ROWS="5" COLS="45" WRAP="SOFT">' . $description . '</TEXTAREA>
   </TD>
  </TR>
  <TR>
   <TD>
    <B>' . _XF_SNP_LANGUAGE . ':</B>
    <BR>
    ' . html_build_select_box_from_arrays($SCRIPT_LANGUAGE_ids, $SCRIPT_LANGUAGE_val, 'language', $SCRIPT_LANGUAGE_ids[0], false) . '
    </TD>
   <TD>
    <B>' . _XF_SNP_CATEGORY . ':</B>
    <BR>
    ' . html_build_select_box_from_arrays($SCRIPT_CATEGORY_ids, $SCRIPT_CATEGORY_val, 'category', $SCRIPT_CATEGORY_ids[0], false) . '
     </TD>
  </TR>
  <TR>
   <TD COLSPAN="2">
    <B>' . _XF_SNP_VERSION . ':</B>
    <BR>
    <INPUT TYPE="TEXT" NAME="version" SIZE="10" MAXLENGTH="15">
   </TD>
  </TR>
  <TR>
   <TD COLSPAN="2" ALIGN="MIDDLE">
    <B>' . _XF_SNP_MAKESUREALLCOMPLETE . '</B>
    <BR>
    <INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="' . _XF_G_SUBMIT . '">' . '
   </TD>
  </TR>
 </TABLE>
</FORM>';

    $xoopsTpl->assign('main', $main);

    require XOOPS_ROOT_PATH . '/footer.php';
} else {
    redirect_header(XOOPS_URL . '/user.php', 2, _NOPERM);

    exit;
}
