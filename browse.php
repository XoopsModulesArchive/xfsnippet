<?php
// $Id: browse.php,v 1.1 2003/12/08 11:27:05 predator Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/vars.php';

require_once XOOPS_ROOT_PATH . '/header.php';

$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
$cat = $_GET['cat'] ?? 0;
$lang = $_GET['lang'] ?? 0;
$type = $_GET['type'] ?? 0;
$license = $_GET['license'] ?? 0;
$text = $_GET['text'] ?? 0;

$GLOBALS['xoopsOption']['template_main'] = 'xfsnippet_browse.html';
$xoopsTpl->assign('header', snippet_header(_XF_SNP_FINDSNIPPET . ' | ' . _XF_SNP_SNIPPETS));
$content = '';

if (!$cat && !$lang) {
    redirect_header(XOOPS_URL . '/modules/xfsnippet', 0);
}

$sql = 'SELECT u.uname,s.description,s.snippet_id,s.name,s.hits,s.rating,s.votes,s.comments ';
$sql .= ' FROM ' . $xoopsDB->prefix('xf_snippet') . ' s,xoops_users u ';
if ('' != $text) {
    $sql .= ',' . $xoopsDB->prefix('xf_snippet_version') . ' v ';
}
$sql .= ' WHERE u.uid=s.created_by ';
if ($lang && 100 != $lang) {
    $sql .= " AND s.language=$lang";
}
if ($cat && 100 != $cat) {
    $sql .= " AND s.category=$cat";
}
if ($type && 100 != $type) {
    $sql .= " AND s.type=$type";
}
if ($license && $license != count($SCRIPT_LICENSE)) {
    $sql .= " AND s.license=$license";
}
if ('' != $text) {
    $sql .= ' AND v.snippet_id=s.snippet_id';

    $sql .= " AND (s.name LIKE '%" . $text . "%' || s.description LIKE '%" . $text . "%' || v.code LIKE '%" . $text . "%')";

    $sql .= ' GROUP BY s.snippet_id';
}
$result = $xoopsDB->query($sql);
$rows = $xoopsDB->getRowsNum($result);

if ((!$type || 100 == $type) && (!$license || $license == count($SCRIPT_LICENSE))) {
    $sql2 = 'SELECT u.uname,sp.description,sp.snippet_package_id,sp.name ';

    $sql2 .= ' FROM ' . $xoopsDB->prefix('xf_snippet_package') . ' sp,xoops_users u ';

    if ('' != $text) {
        $sql2 .= ', ' . $xoopsDB->prefix('xf_snippet_package_item') . ' pi,' . $xoopsDB->prefix('xf_snippet_version') . ' sv ';

        $sql2 .= ', ' . $xoopsDB->prefix('xf_snippet_package_version') . ' pv';
    }

    $sql2 .= ' WHERE u.uid=sp.created_by ';

    if ($lang && 100 != $lang) {
        $sql2 .= " AND sp.language='$lang'";
    }

    if ($cat && 100 != $cat) {
        $sql2 .= " AND sp.category='$cat'";
    }

    if ('' != $text) {
        $sql2 .= ' AND sp.snippet_package_id=pv.snippet_package_id';

        $sql2 .= ' AND pv.snippet_package_version_id=pi.snippet_package_version_id';

        $sql2 .= ' AND sv.snippet_version_id=pi.snippet_version_id';

        $sql2 .= " AND (sp.name LIKE '%" . $text . "%' || sp.description LIKE '%" . $text . "%' || sv.code LIKE '%" . $text . "%')";

        $sql2 .= ' GROUP BY sp.snippet_package_id';
    }

    $result2 = $xoopsDB->query($sql2);

    $rows2 = $xoopsDB->getRowsNum($result2);
}

if ((!$result || $rows < 1) && (!$result2 || $rows2 < 1)) {
    $content .= '
<H4>' . _XF_SNP_NOPACKAGESSNIPPETSFOUND . '</H4>';
} else {
    $content .= "<table border='0' width='100%'>" . "<tr class='head'>" . '<td><b>' . _XF_SNP_TITLE . '</b></td>' . '<td align=right><b>' . _XF_SNP_CREATOR . '</b></td>' . '</tr>';

    /**
     * List packages if there are any
     */

    if ($rows2 > 0) {
        $content .= "
			<TR class='head'><TD COLSPAN='2'><B>" . _XF_SNP_PACKAGESOFSNIPPETS . '</B></TD>';
    }

    for ($i = 0; $i < $rows2; $i++) {
        $content .= '
 <TR CLASS="' . ($i % 2 > 0 ? 'even' : 'odd') . '">
  <TD VALIGN="TOP">
   <A HREF="' . XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&snippet_id=' . unofficial_getDBResult($result2, $i, 'snippet_package_id') . '"><B>' . htmlspecialchars(unofficial_getDBResult($result2, $i, 'name'), ENT_QUOTES | ENT_HTML5) . '</B></A>
  </TD>
  <TD VALIGN="TOP" ALIGN="RIGHT">
   ' . unofficial_getDBResult($result2, $i, 'uname') . '
  </TD>
 </TR>
 <TR CLASS="' . ($i % 2 > 0 ? 'even' : 'odd') . '">
  <TD>
   ' . htmlspecialchars(unofficial_getDBResult($result2, $i, 'description'), ENT_QUOTES | ENT_HTML5) . '
  </TD>
  <TD>
   &nbsp;
  </TD>
 </TR>';
    }

    /**
     * List snippets if there are any
     */

    if ($rows > 0) {
        $content .= '
 <TR>
  <TD COLSPAN="2">
   &nbsp;
  </TD>
 </TR>
 <TR CLASS="head">
  <TD COLSPAN="3">
   <B>' . _XF_SNP_SNIPPETS . '</B>
  </TD>
 </TR>';
    }

    for ($i = 0; $i < $rows; $i++) {
        if (1 == unofficial_getDBResult($result, $i, 'votes')) {
            $votestring = _XF_SNP_ONEVOTE;
        } else {
            $votestring = sprintf(_XF_SNP_NUMVOTES, unofficial_getDBResult($result, $i, 'votes'));
        }

        $content .= '
 <TR CLASS="'
                    . ($i % 2 > 0 ? 'even' : 'odd')
                    . '">
  <TD VALIGN="TOP">
   <A HREF="'
                    . XOOPS_URL
                    . '/modules/xfsnippet/detail.php?type=snippet&snippet_id='
                    . unofficial_getDBResult($result, $i, 'snippet_id')
                    . '"><B>'
                    . htmlspecialchars(unofficial_getDBResult($result, $i, 'name'), ENT_QUOTES | ENT_HTML5)
                    . '</B></A>
  </TD>
  <TD VALIGN="TOP" ALIGN="RIGHT">
   '
                    . unofficial_getDBResult($result, $i, 'uname')
                    . '
  </TD>
 </TR>
 <TR CLASS="'
                    . ($i % 2 > 0 ? 'even' : 'odd')
                    . '">
  <TD>
   '
                    . htmlspecialchars(unofficial_getDBResult($result, $i, 'description'), ENT_QUOTES | ENT_HTML5)
                    . '
  </TD>
  <TD>
   &nbsp;
  </TD>
 </TR>
 <tr><td colspan="2">
  <table width="100%" cellspacing="0" class="outer">
<tr>
    <td colspan="2" class="foot" align="center">
    <div><b>'
                    . _XF_SNP_HITS
                    . '</b> '
                    . unofficial_getDBResult($result, $i, 'hits')
                    . ' <b>'
                    . _XF_SNP_RATINGC
                    . '</b> '
                    . number_format(unofficial_getDBResult($result, $i, 'rating'), 2)
                    . ' ('
                    . $votestring
                    . ')</div>
    <a href="'
                    . XOOPS_URL
                    . '/modules/xfsnippet/ratefile.php?snippet_id='
                    . unofficial_getDBResult($result, $i, 'snippet_id')
                    . '">'
                    . _XF_SNP_RATETHISFILE
                    . '</a> | <a target="_top" href="mailto:?subject='
                    . rawurlencode(sprintf(_MD_INTRESTLINK, $xoopsConfig['sitename']))
                    . '&amp;body='
                    . rawurlencode(sprintf(_MD_INTLINKFOUND, $xoopsConfig['sitename']) . ':  ' . XOOPS_URL . '/modules/xfsnippet/detail.php?type=snippet&snippet_id=&snippet_id=' . unofficial_getDBResult($result, $i, 'snippet_id'))
                    . '">'
                    . _XF_SNP_TELLAFRIEND
                    . '</a> | <a href="'
                    . XOOPS_URL
                    . '/modules/xfsnippet/detail.php?type=snippet&snippet_id='
                    . unofficial_getDBResult($result, $i, 'snippet_id')
                    . '">'
                    . _COMMENTS
                    . ' ('
                    . unofficial_getDBResult($result, $i, 'comments')
                    . ')</a>
    </td>
  </tr>
</table></td></tr>';
    }

    $content .= '
</TABLE>';
}

$xoopsTpl->assign('lang_ratingc', _XF_SNP_RATINGC);

$xoopsTpl->assign('content', $content);

require XOOPS_ROOT_PATH . '/footer.php';
