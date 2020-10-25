<?php

require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/html.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/vars.php';

require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
require_once XOOPS_ROOT_PATH . '/class/module.errorhandler.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';

$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
$typetree = new XoopsTree($xoopsDB->prefix('xf_snippet_type'), 'type_id', 'name');
$lantree = new XoopsTree($xoopsDB->prefix('xf_snippet_language'), 'type_id', 'name');
$cattree = new XoopsTree($xoopsDB->prefix('xf_snippet_category'), 'type_id', 'name');

if (empty($xoopsUser) and !$xoopsModuleConfig['anonpost']) {
    redirect_header(XOOPS_URL . '/user.php', 2, _MD_MUSTREGFIRST);

    exit();
}
if (!empty($_POST['submit'])) {
    $submitter = !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

    if ($_POST['post_changes']) {
        if ($name && $description && 100 != $license && 100 != $language && 100 != $category && 100 != $type && $version && $code) {
            $sql = 'INSERT INTO '
                      . $xoopsDB->prefix('xf_snippet')
                      . ' (category,created_by,name,description,type,language,license)'
                      . " VALUES ('$category','"
                      . $xoopsUser->getVar('uid')
                      . "','"
                      . $ts->addSlashes($name)
                      . "',"
                      . "'"
                      . $ts->addSlashes($description)
                      . "','$type','$language','$license')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                $feedback = addMsg(_XF_SNP_SNIPPETINSERTERROR . ' - ' . $xoopsDB->error());
            } else {
                $feedback = addMsg(_XF_SNP_SNIPPETADDED);

                $snippet_id = $xoopsDB->getInsertId();

                $sql = 'INSERT INTO '
                              . $xoopsDB->prefix('xf_snippet_version')
                              . ' (snippet_id,changes,version,submitted_by,date,code)'
                              . " VALUES ('$snippet_id','"
                              . $ts->addSlashes($changes)
                              . "',"
                              . "'"
                              . $ts->addSlashes($version)
                              . "','"
                              . $xoopsUser->getVar('uid')
                              . "',"
                              . "'"
                              . time()
                              . "','"
                              . $ts->addSlashes($code)
                              . "')";

                $result = $xoopsDB->queryF($sql);

                if (!$result) {
                    $feedback .= addMsg(_XF_SNP_SNIPPETVERSIONINSERTERROR . ' - ' . $xoopsDB->error());
                } else {
                    redirect_header('browse.php?by=lang&lang=' . $language, 2, _XF_SNP_SNIPPETVERSIONADDED);
                }
            }
        } else {
            redirect_header('submit.php?', 2, _XF_SNP_GOBACKFILLREQFIELDS);
        }
    }
} else {
    $GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_submit.html';

    require XOOPS_ROOT_PATH . '/header.php';

    ob_start();

    xoopsCodeTarea('description', 85, 5);

    $xoopsTpl->assign('xoops_codes', ob_get_contents());

    ob_end_clean();

    ob_start();

    xoopsSmilies('description');

    $xoopsTpl->assign('xoops_smilies', ob_get_contents());

    ob_end_clean();

    ob_start();

    xoopsCodeTarea('code', 85, 30);

    $xoopsTpl->assign('xoops_code_codes', ob_get_contents());

    ob_end_clean();

    $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPET));

    $xoopsTpl->assign('lang_note', _XF_SNP_NOTE);

    $xoopsTpl->assign('feedback', $feedback);

    $xoopsTpl->assign('lang_youcanpostbybrowse', _XF_SNP_YOUCANPOSTSNIPPETBYBROWSE);

    $xoopsTpl->assign('lang_tags', _XF_SNP_TAGS);

    $xoopsTpl->assign('lang_title', _XF_SNP_TITLE);

    $xoopsTpl->assign('lang_firstposted', _XF_SNP_FIRSTPOSTEDVERSION);

    $xoopsTpl->assign('lang_desc', _XF_G_DESCRIPTION);

    $xoopsTpl->assign('lang_youcanpost', _XF_SNP_YOUCANPOSTSNIPPET);

    $xoopsTpl->assign('lang_license', _XF_SNP_LICENSE);

    $xoopsTpl->assign('lang_category', _XF_SNP_CATEGORY);

    $xoopsTpl->assign('lang_language', _XF_SNP_LANGUAGE);

    $xoopsTpl->assign('lang_type', _XF_SNP_TYPE);

    $xoopsTpl->assign('lang_version', _XF_SNP_VERSION);

    $xoopsTpl->assign('lang_pastecode', _XF_SNP_PASTECODEHERE);

    $xoopsTpl->assign('lang_snippetsubmitmes', _XF_SNP_SNIPPETSUBMITMESSAGE);

    $xoopsTpl->assign('lang_submit', _SUBMIT);

    $xoopsTpl->assign('lang_cancel', _CANCEL);

    ob_start();

    $cattree->makeMySelBox('name', 'name', 100, 100, 'category');

    $selcatbox = ob_get_contents();

    ob_end_clean();

    $xoopsTpl->assign('category_selcatbox', $selcatbox);

    ob_start();

    $typetree->makeMySelBox('name', 'name', 100, 100, 'type');

    $seltypbox = ob_get_contents();

    ob_end_clean();

    $xoopsTpl->assign('category_seltypbox', $seltypbox);

    ob_start();

    $lantree->makeMySelBox('name', 'name', 100, 100, 'language');

    $sellanbox = ob_get_contents();

    ob_end_clean();

    $xoopsTpl->assign('category_sellanbox', $sellanbox);

    $xoopsTpl->assign('category_sellicbox', html_build_select_box_from_array($SCRIPT_LICENSE, 'license'));

    require XOOPS_ROOT_PATH . '/footer.php';
}
