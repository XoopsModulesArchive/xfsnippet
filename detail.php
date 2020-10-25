<?php

require_once '../../mainfile.php';
$GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_detail.html';
require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/html.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/vars.php';

require_once XOOPS_ROOT_PATH . '/header.php';

$snippet_id = $_GET['snippet_id'] ?? 0;
$snippet_id = (int)$snippet_id;
$type = $_GET['type'] ?? 0;
$version = $_GET['version'] ?? 0;
$uname = $_GET['uname'] ?? 0;

$sql = sprintf('UPDATE %s SET hits = hits+1 WHERE snippet_id = %u', $xoopsDB->prefix('xf_snippet'), $snippet_id);
$xoopsDB->queryF($sql);

$content2 = '';

if ('snippet' == $type) {
    if (!snippet_show_snippet_details($snippet_id)) {
        redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_SNIPPETDOESNOTEXIST);
    }

    $xoopsTpl->assign('header', snippet_header(_XF_SNP_CODESNIPPET));

    $sql = 'SELECT u.uname,sv.snippet_version_id,pi.snippet_version_id as packaged, sv.version,sv.date,sv.changes'
              . ' FROM xoops_users as u JOIN '
              . $xoopsDB->prefix('xf_snippet_version')
              . ' as sv'
              . ' LEFT JOIN '
              . $xoopsDB->prefix('xf_snippet_package_item')
              . ' as pi'
              . ' ON sv.snippet_version_id=pi.snippet_version_id'
              . ' WHERE u.uid=sv.submitted_by'
              . " AND snippet_id='$snippet_id'"
              . ' GROUP BY sv.snippet_version_id'
              . ' ORDER BY sv.snippet_version_id DESC';

    $result = $xoopsDB->query($sql);

    $rows = $xoopsDB->getRowsNum($result);

    if (!$result || $rows < 1) {
        $content2 .= '
<H4>' . _XF_SNP_VERSIONSNOTFOUND . '</H4>';
    } else {
        $content2 .= '
<P>
<B>' . _XF_SNP_VERSIONSOFSNIPPET . ':</B>
<BR>
<TABLE BORDER="0" WIDTH="100%">
 <TR CLASS="head">
  <TD>
   <B>' . _XF_SNP_VERSIONCLICK . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_PACKAGES . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_DATEPOSTED . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_SNIPPETID . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_AUTHOR . '</B>
  </TD>
  <TD>
  </TD>
 </TR>';

        for ($i = 0; $i < $rows; $i++) {
            $row = $xoopsDB->fetchArray($result);

            if (!$version && 0 == $i) {
                $version = $row['snippet_version_id'];
            }

            if (!$uname && 0 == $i) {
                $uname = $row['uname'];
            }

            $content2 .= '
 <TR CLASS="' . ($i % 2 > 0 ? 'even' : 'odd') . '">
  <TD VALIGN="TOP">
   <A HREF="' . XOOPS_URL . '/modules/xfsnippet/detail.php?type=snippet&snippet_id=' . $snippet_id . '&version=' . $row['snippet_version_id'] . '">' . '<B>' . htmlspecialchars($row['version'], ENT_QUOTES | ENT_HTML5) . '</B></A>
  </TD>';

            if ($row['packaged']) {
                $sql = 'SELECT p.name, p.snippet_package_id'
                            . ' FROM '
                            . $xoopsDB->prefix('xf_snippet_package')
                            . ' as p'
                            . ', '
                            . $xoopsDB->prefix('xf_snippet_package_version')
                            . ' as pv'
                            . ', '
                            . $xoopsDB->prefix('xf_snippet_package_item')
                            . ' as pi'
                            . ' WHERE p.snippet_package_id=pv.snippet_package_id'
                            . ' AND pv.snippet_package_version_id=pi.snippet_package_version_id'
                            . ' AND pi.snippet_version_id='
                            . $row['packaged']
                            . ' GROUP BY p.snippet_package_id';

                $rs = $xoopsDB->query($sql);

                $content2 .= '
  <TD>';

                if ($rs) {
                    while (false !== ($mypackage = $xoopsDB->fetchArray($rs))) {
                        $content2 .= '
   <A HREF="' . XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&snippet_id=' . $mypackage['snippet_package_id'] . '">' . $mypackage['name'] . '</a>
   <BR>';
                    }
                }

                $content2 .= '
  </TD>';
            } else {
                $content2 .= '
  <TD>
   &nbsp;
  </TD>';
            }

            $content2 .= '
  <TD VALIGN="TOP">
   ' . date(_DATESTRING, $row['date']) . '
  </TD>
  <TD VALIGN="TOP">
   ' . $row['snippet_version_id'] . '
  </TD>
  <TD VALIGN="TOP">
   ' . $row['uname'] . '
  </TD>
  <TD ALIGN="CENTER" VALIGN="TOP">';

            if ($xoopsUser && $xoopsUser->getVar('uname') == $row['uname']) {
                $content2 .= "
   <A HREF='" . XOOPS_URL . '/modules/xfsnippet/delete.php?type=snippet&snippet_id=' . $snippet_id . '&snippet_version_id=' . $row['snippet_version_id'] . "'><img src='../xfsnippet/images/trash.png' alt='' width='16' height='16' border='0'></A>";
            }

            $content2 .= '
  </TD>
 </TR>
 <TR CLASS="' . ($i % 2 > 0 ? 'even' : 'odd') . '">
  <TD COLSPAN=6>
   ' . _XF_SNP_CHANGESSINCELASTVERSION . ':
   <BR>
   ' . htmlspecialchars($row['changes'], ENT_QUOTES | ENT_HTML5) . '
  </TD>
 </TR>';
        }

        $content2 .= '
</TABLE>';

        if ($xoopsUser && $xoopsUser->getVar('uname') == $uname) {
            $content2 .= '<P><B>
		<A HREF="' . XOOPS_URL . '/modules/xfsnippet/addversion.php?type=snippet&snippet_id=' . $snippet_id . '">' . _XF_SNP_SUBMITNEWSNIPPETVERSION . '</A></B>
        <BR>' . _XF_SNP_YOUCANSUBMITIFMODIFIED . '</P>';
        } else {
            unset($isadmin);

            if ($xoopsUser && $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
                $isadmin = true;

                $content2 .= '<P><B>
	    <A HREF="' . XOOPS_URL . '/modules/xfsnippet/addversion.php?type=snippet&snippet_id=' . $snippet_id . '">' . _XF_SNP_SUBMITNEWSNIPPETVERSION . '</A></B>
        <BR>' . _XF_SNP_YOUCANSUBMITIFMODIFIED . '</P>';
            }
        }
    }

    $sql = 'SELECT v.code,v.version,s.comments' . ' FROM ' . $xoopsDB->prefix('xf_snippet_version') . ' as v' . ', ' . $xoopsDB->prefix('xf_snippet') . ' as s' . ' WHERE s.snippet_id=v.snippet_id' . ' AND s.snippet_id=' . $snippet_id . ' AND v.snippet_version_id=' . $version;

    $result = $xoopsDB->query($sql);

    if (preg_match("#\[php\](.*?)\[/php\]#si", unofficial_getDBResult($result, 0, 'code'))) {
        $code = bbencode_highlight_php(unofficial_getDBResult($result, 0, 'code'));
    } else {
        $code = $myts->xoopsCodeDecode(unofficial_getDBResult($result, 0, 'code'));

        $code = $myts->nl2Br($code);
    }

    $content2 .= '<P><B>' . _XF_SNP_VERSION . ': ' . htmlspecialchars(unofficial_getDBResult($result, 0, 'version'), ENT_QUOTES | ENT_HTML5) . '<B>
<BR><DIV CLASS="xoopsCode">' . $code . '</DIV>';

    $xoopsTpl->assign('lang_ratingc', _XF_SNP_RATINGC);

    $xoopsTpl->assign('content', $content);

    $xoopsTpl->assign('content2', $content2);

    require XOOPS_ROOT_PATH . '/include/comment_view.php';

    require_once XOOPS_ROOT_PATH . '/footer.php';
} elseif ('package' == $type) {
    if (!snippet_show_package_details($snippet_id)) {
        redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_PACKAGEDOESNOTEXIST);
    }

    $xoopsTpl->assign('header', snippet_header(_XF_SNP_SNIPPETPACKAGE));

    $sql = 'SELECT u.uname,spv.snippet_package_version_id,spv.version,spv.date' . ' FROM ' . $xoopsDB->prefix('xf_snippet_package_version') . ' spv,xoops_users u' . ' WHERE u.uid=spv.submitted_by' . " AND snippet_package_id='$snippet_id'" . ' ORDER BY spv.snippet_package_version_id DESC';

    $result = $xoopsDB->query($sql);

    $rows = $xoopsDB->getRowsNum($result);

    $uname = unofficial_getDBResult($result, 0, 'uname');

    if (!$result || $rows < 1) {
        redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_PACKAGENOVERSIONS);
    } else {
        $content = '
<H4>' . _XF_SNP_VERSIONSOFPACKAGE . ':</H4>
<P>
<TABLE BORDER="0" WIDTH="100%">
 <TR CLASS="head">
  <TD>
   <B>' . _XF_SNP_PACKAGEVERSION . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_DATEPOSTED . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_AUTHOR . '</B>
  </TD>
  <TD>
  </TD>
 </TR>';

        if (!$package_version) {
            $package_version = unofficial_getDBResult($result, 0, 'snippet_package_version_id');
        }

        for ($i = 0; $i < $rows; $i++) {
            $content .= '
 <TR CLASS="' . ($i % 2 > 0 ? 'even' : 'odd') . '">
  <TD>
   <A HREF="' . XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&snippet_id=' . $snippet_id . '&package_version=' . unofficial_getDBResult($result, $i, 'snippet_package_version_id') . '">' . '<B>' . htmlspecialchars(unofficial_getDBResult($result, $i, 'version'), ENT_QUOTES | ENT_HTML5) . '</B></A>
  </TD>
  <TD>
   ' . date(_DATESTRING, unofficial_getDBResult($result, $i, 'date')) . '
  </TD>
  <TD>
   ' . unofficial_getDBResult($result, $i, 'uname') . '
  </TD>
  <TD ALIGN="MIDDLE">';

            if ($xoopsUser && $xoopsUser->getVar('uname') == unofficial_getDBResult($result, $i, 'uname')) {
                $content .= '
   <A HREF="'
                            . XOOPS_URL
                            . '/modules/xfsnippet/add_snippet_to_package.php?snippet_package_version_id='
                            . unofficial_getDBResult($result, $i, 'snippet_package_version_id')
                            . '">'
                            . '<IMG SRC="'
                            . XOOPS_URL
                            . '/modules/xfsnippet/images/pencil.png" WIDTH="16" HEIGHT="16" BORDER="0"></A>

   <A HREF="'
                            . XOOPS_URL
                            . '/modules/xfsnippet/delete.php?type=package&snippet_id='
                            . $snippet_id
                            . '&snippet_package_version_id='
                            . unofficial_getDBResult($result, $i, 'snippet_package_version_id')
                            . '">'
                            . '<IMG SRC="'
                            . XOOPS_URL
                            . '/modules/xfsnippet/images/trash.png" WIDTH="16" HEIGHT="16" BORDER="0"></A>';
            }

            $content .= '
  </TD>
 </TR>';
        }

        $content .= '
</TABLE>';

        if ($xoopsUser && $xoopsUser->getVar('uname') == $uname) {
            $content .= '
<P>
<B><A HREF="' . XOOPS_URL . '/modules/xfsnippet/addversion.php?type=package&snippet_id=' . $snippet_id . '">' . _XF_SNP_SUBMITNEWVERSION . '</A></B>
<BR>
' . _XF_SNP_YOUCANSUBMITIFMODIFIEDPACKAGE . '
</P>';
        }
    }

    $content .= '
<P>
<HR>
<P>
<H4>' . _XF_SNP_LATESTPACKAGEVERSION . ': ' . htmlspecialchars(unofficial_getDBResult($result, 0, 'version'), ENT_QUOTES | ENT_HTML5) . '</H4>
<P>
<P>';

    $sql = 'SELECT spi.snippet_version_id,sv.version,s.name,s.snippet_id,u.uname as user_name'
              . ' FROM '
              . $xoopsDB->prefix('xf_snippet')
              . ' s,'
              . $xoopsDB->prefix('xf_snippet_version')
              . ' sv,'
              . $xoopsDB->prefix('xf_snippet_package_item')
              . ' spi,xoops_users u'
              . ' WHERE s.snippet_id=sv.snippet_id'
              . ' AND u.uid=sv.submitted_by'
              . ' AND sv.snippet_version_id=spi.snippet_version_id'
              . " AND spi.snippet_package_version_id='$package_version'";

    $result = $xoopsDB->query($sql);

    $rows = $xoopsDB->getRowsNum($result);

    $content .= '
<P>
<H4>' . _XF_SNP_SNIPPETSINPACKAGE . ':</H4>
<P>
<TABLE BORDER="0" WIDTH="100%">';

    if (!$result || $rows < 1) {
        $content .= $xoopsDB->error();

        $content .= '
 <TR>
  <TD COLSPAN="4">
   <B>' . _XF_SNP_NOSNIPPETSINPACKAGE . '</B>
  </TD>
 </TR>';
    } else {
        $content .= '
 <TR CLASS="head">
  <TD>
   <B>' . _XF_SNP_TITLE . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_VERSION . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_AUTHOR . '</B>
  </TD>
 </TR>';

        // get the newest version, so we can display it's code

        // $version = unofficial_getDBResult($result,0,'snippet_version_id');

        for ($i = 0; $i < $rows; $i++) {
            $content .= '
 <TR class="'
                        . ($i % 2 > 0 ? 'even' : 'odd')
                        . '">
  <TD>
   <A HREF="'
                        . XOOPS_URL
                        . '/modules/xfsnippet/detail.php?type=package&snippet_id='
                        . $snippet_id
                        . '&package_version='
                        . $package_version
                        . '&snippet_id='
                        . unofficial_getDBResult($result, $i, 'snippet_id')
                        . '&version='
                        . unofficial_getDBResult($result, $i, 'snippet_version_id')
                        . '">'
                        . htmlspecialchars(unofficial_getDBResult($result, $i, 'name'), ENT_QUOTES | ENT_HTML5)
                        . '</A>
  </TD>
  <TD>
   '
                        . htmlspecialchars(unofficial_getDBResult($result, $i, 'version'), ENT_QUOTES | ENT_HTML5)
                        . '
  </TD>
  <TD>
   '
                        . unofficial_getDBResult($result, $i, 'user_name')
                        . '
  </TD>
 </TR>';
        }
    }

    $content .= '
</TABLE>';

    if ($package_version && $snippet_id && $version) {
        snippet_show_snippet_header($snippet_id);

        snippet_show_snippet($snippet_id, $version);
    }

    $xoopsTpl->assign('content', $content);

    $xoopsTpl->assign('lang_comments', _COMMENTS);

    require XOOPS_ROOT_PATH . '/include/comment_view.php';

    require XOOPS_ROOT_PATH . '/footer.php';
} elseif ('packagever' == $type) {
    snippet_header(_XF_SNP_SNIPPETLIBRARY);

    snippet_show_package_details($snippet_id);

    snippet_show_package_snippets($snippet_id);

    snippet_show_snippet($snippet_id, $version);

    $xoopsTpl->assign('content', $content);

    $xoopsTpl->assign('lang_comments', _COMMENTS);

    require XOOPS_ROOT_PATH . '/include/comment_view.php';

    require XOOPS_ROOT_PATH . '/footer.php';
} else {
    redirect_header(XOOPS_URL . '/user.php', 2, _NOPERM);

    exit;
}
