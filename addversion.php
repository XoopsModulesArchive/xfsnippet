<?php

require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/html.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/vars.php';

require_once XOOPS_ROOT_PATH . '/class/module.errorhandler.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';

$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
if (empty($xoopsUser) and !$xoopsModuleConfig['anonpost']) {
    redirect_header(XOOPS_URL . '/user.php', 2, _MD_MUSTREGFIRST);

    exit();
}

if (!empty($_POST['submit'])) {
    $submitter = !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

    if ('snippet' == $_POST['type']) {
        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet') . " WHERE snippet_id='$snippet_id'";

        $result = $xoopsDB->query($sql);

        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_SNIPPETDOESNOTEXIST);
        }

        if ($_POST['post_changes']) {
            if ($changes && $version && $code) {
                $sql = 'INSERT INTO '
                          . $xoopsDB->prefix('xf_snippet_version')
                          . ' (snippet_id,changes,version,submitted_by,date,code)'
                          . "VALUES ('$snippet_id','"
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
                    $feedback = _XF_SNP_SNIPPETVERSIONINSERTERROR;

                    $feedback .= $xoopsDB->error();
                } else {
                    redirect_header('detail.php?type=snippet&snippet_id=' . $snippet_id, 2, _XF_SNP_SNIPPETVERSIONADDED);
                }
            } else {
                $feedback .= _XF_SNP_GOBACKFILLALLINFO;

                exit;
            }
        }
    }
} else {
    $GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_addsnippet.html';

    require XOOPS_ROOT_PATH . '/header.php';

    ob_start();

    xoopsCodeTarea('changes', 85, 5);

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

    $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPETVERSION));

    $xoopsTpl->assign('lang_note', _XF_SNP_NOTE);

    $xoopsTpl->assign('lang_youcanpostbybrowse', _XF_SNP_IFMODIFIEDDOSHARE);

    $xoopsTpl->assign('lang_tags', _XF_SNP_TAGS);

    $xoopsTpl->assign('lang_title', _XF_SNP_TITLE);

    $xoopsTpl->assign('lang_firstposted', _XF_SNP_FIRSTPOSTEDVERSION);

    $xoopsTpl->assign('lang_changes', _XF_SNP_CHANGES);

    $xoopsTpl->assign('lang_youcanpost', _XF_SNP_YOUCANPOSTSNIPPET);

    $xoopsTpl->assign('lang_license', _XF_SNP_LICENSE);

    $xoopsTpl->assign('lang_category', _XF_SNP_CATEGORY);

    $xoopsTpl->assign('lang_language', _XF_SNP_LANGUAGE);

    $xoopsTpl->assign('lang_type', _XF_SNP_TYPE);

    $xoopsTpl->assign('lang_version', _XF_SNP_VERSION);

    $xoopsTpl->assign('lang_pastecode', _XF_SNP_PASTECODEHERE);

    $xoopsTpl->assign('lang_snippetsubmitmes', _XF_SNP_SNIPPETSUBMITMESSAGE);

    $xoopsTpl->assign('snippet_id', $snippet_id);

    $xoopsTpl->assign('feedback', $feedback);

    $xoopsTpl->assign('lang_submit', _SUBMIT);

    $xoopsTpl->assign('lang_cancel', _CANCEL);

    require XOOPS_ROOT_PATH . '/footer.php';
}

if ('package' == $_POST['type']) {
    $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_package') . " WHERE snippet_package_id='$snippet_id'");

    if (!$result || $xoopsDB->getRowsNum($result) < 1) {
        redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_SNIPPETDOESNOTEXIST);
    }

    if ($_GET['post_changes']) {
        if ($changes && $snippet_package_id) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xf_snippet_package_version') . ' ' . '(snippet_package_id,changes,version,submitted_by,date) ' . "VALUES ('$snippet_package_id','" . $ts->addSlashes($changes) . "'," . "'" . $ts->addSlashes($version) . "','" . $xoopsUser->getVar(
                'uid'
            ) . "','" . time() . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                $feedback = ' ERROR DOING SNIPPET PACKAGE VERSION INSERT! ';

                $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPETPACKAGE));

                $feedback .= $xoopsDB->error();

                $xoopsTpl->assign('feedback', $feedback);

                require XOOPS_ROOT_PATH . '/footer.php';

                exit;
            }  

            $GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_addsnippettopackage.html';

            require XOOPS_ROOT_PATH . '/header.php';

            $feedback = _XF_SNP_SNIPPETPACKAGEVERSIONADDED;

            $snippet_package_version_id = $xoopsDB->getInsertId();

            $xoopsTpl->assign('header', snippet_header(_XF_SNP_ADDSNIPETTOPACKAGE));

            $content = '<P><FONT COLOR="RED"><B>' . _XF_SNP_IMPORTANT . '</B></FONT><P>' . _XF_SNP_ADDSNIPPETSBEFORELEAVE . '<P>';

            $content .= '<A HREF="' . XOOPS_URL . '/modules/xfsnippet/add_snippet_to_package.php?snippet_package_version_id=' . $snippet_package_version_id . '' > ';
                                        $content .=' . _XF_SNP_ADDSNIPETTOPACKAGE . '</A>';

            $content .= '<P>' . _XF_SNP_ADDSNIPPETSWITHLINK . '<P>';

            $xoopsTpl->assign('feedback', $feedback);

            $xoopsTpl->assign('content', $content);

            require XOOPS_ROOT_PATH . '/footer.php';
        } else {
            $feedback .= _XF_SNP_GOBACKFILLALLINFO;

            exit();
        }

        $GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_addpackage.html';

        require XOOPS_ROOT_PATH . '/header.php';

        ob_start();

        xoopsCodeTarea('changes', 85, 5);

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

        $xoopsTpl->assign('header', snippet_header(_XF_SNP_SUBMITNEWSNIPPETVERSION));

        $xoopsTpl->assign('lang_note', _XF_SNP_NOTE);

        $xoopsTpl->assign('lang_youcanpostbybrowse', XF_SNP_IFMODIFIEDPACKAGEDOSHARE);

        $xoopsTpl->assign('lang_tags', _XF_SNP_TAGS);

        $xoopsTpl->assign('lang_title', _XF_SNP_TITLE);

        $xoopsTpl->assign('lang_firstposted', _XF_SNP_FIRSTPOSTEDVERSION);

        $xoopsTpl->assign('lang_changes', _XF_SNP_CHANGES);

        $xoopsTpl->assign('lang_youcanpost', _XF_SNP_YOUCANPOSTSNIPPET);

        $xoopsTpl->assign('lang_license', _XF_SNP_LICENSE);

        $xoopsTpl->assign('lang_category', _XF_SNP_CATEGORY);

        $xoopsTpl->assign('lang_language', _XF_SNP_LANGUAGE);

        $xoopsTpl->assign('lang_type', _XF_SNP_TYPE);

        $xoopsTpl->assign('lang_version', _XF_SNP_VERSION);

        $xoopsTpl->assign('lang_pastecode', _XF_SNP_PASTECODEHERE);

        $xoopsTpl->assign('lang_snippetsubmitmes', _XF_SNP_SNIPPETSUBMITMESSAGE);

        $xoopsTpl->assign('snippet_id', $snippet_id);

        $xoopsTpl->assign('snippet_package_id', $snippet_id);

        $xoopsTpl->assign('feedback', $feedback);

        $xoopsTpl->assign('lang_submit', _SUBMIT);

        $xoopsTpl->assign('lang_cancel', _CANCEL);

        require XOOPS_ROOT_PATH . '/footer.php';
    }
}
