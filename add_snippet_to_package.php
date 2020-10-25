<?php

require_once 'header.php';

require_once XOOPS_ROOT_PATH . '/modules/xfsnippet/include/snippet.php';
$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
function handle_add_exit()
{
    global $suppress_nav;

    if ($suppress_nav) {
        echo '';
    } else {
        include '../../footer.php';
    }

    exit;
}

if (empty($xoopsUser) and !$xoopsModuleConfig['anonpost']) {
    redirect_header(XOOPS_URL . '/user.php', 2, _MD_MUSTREGFIRST);

    exit();
}
if ($_POST['post_changes']) {
    if ($snippet_package_version_id && $available) {
        foreach ($available as $snippet_version_id) {
            $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_package_version') . " WHERE submitted_by='" . $xoopsUser->getVar('uid') . "'" . " AND snippet_package_version_id='$snippet_package_version_id'";

            $result = $xoopsDB->query($sql);

            if (!$result || $xoopsDB->getRowsNum($result) < 1) {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&id=' . $snippet_package_version_id, 2, _XF_SNP_ONLYCREATORCANADDTOPACKAGE);
            }

            /**
             * make sure the snippet_version_id exists
             */

            $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_version') . " WHERE snippet_version_id='$snippet_version_id'";

            $result = $xoopsDB->query($sql);

            if (!$result || $xoopsDB->getRowsNum($result) < 1) {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_SNIPPETDOESNOTEXIST);
            }

            /**
             * make sure the snippet_version_id isn't already in this package
             */

            $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_snippet_package_item') . " WHERE snippet_package_version_id='$snippet_package_version_id'" . " AND snippet_version_id='$snippet_version_id'";

            $result = $xoopsDB->query($sql);

            if ($result && $xoopsDB->getRowsNum($result) > 0) {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/', 2, _XF_SNP_SNIPPETALREADYADDEDTOPACKAGE);
            }

            /**
             * create the snippet version
             */

            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xf_snippet_package_item') . ' (snippet_package_version_id,snippet_version_id)' . "VALUES ('$snippet_package_version_id','$snippet_version_id')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&id=' . $snippet_package_version_id, 2, _XP_SNP_PACKAGEVERSIONINSERTERROR);
            }
        }

        redirect_header(XOOPS_URL . '/modules/xfsnippet/detail.php?type=package&id=' . $snippet_package_version_id, 2, _XF_SNP_SNIPPETVERSIONADDED);
    } else {
        addMsg(_XF_SNP_GOBACKFILLALLINFO);
    }
}

require_once XOOPS_ROOT_PATH . '/header.php';
echo snippet_header(_XF_SNP_ADDPACKAGESNIPPETS);

$sql = 'SELECT sp.name,spv.version' . ' FROM ' . $xoopsDB->prefix('xf_snippet_package') . ' sp,' . $xoopsDB->prefix('xf_snippet_package_version') . ' spv' . ' WHERE sp.snippet_package_id=spv.snippet_package_id' . " AND spv.snippet_package_version_id='$snippet_package_version_id'";
$result = $xoopsDB->query($sql);
echo '
<TABLE BORDER="0">
 <TR>
  <TD VALIGN=TOP>
   <B>' . _XF_SNP_PACKAGE . ':</B>
   <BR>
   ' . htmlspecialchars(unofficial_getDBResult($result, 0, 'name'), ENT_QUOTES | ENT_HTML5) . ' v' . htmlspecialchars(unofficial_getDBResult($result, 0, 'version'), ENT_QUOTES | ENT_HTML5) . '
   <P>' . _XF_SNP_CANUSEFORMREPEATEDLY . '
   <P>';

$sql = 'SELECT sv.snippet_version_id, s.name, sv.version' . ' FROM ' . $xoopsDB->prefix('xf_snippet') . ' as s' . ',' . $xoopsDB->prefix('xf_snippet_version') . ' as sv' . ' WHERE s.snippet_id=sv.snippet_id' . ' AND s.created_by=' . $xoopsUser->getVar('uid');
$result = $xoopsDB->query($sql);
while (false !== ($row = $xoopsDB->fetchArray($result))) {
    $mysnippets[] = $row;
}

$sql = 'SELECT spi.snippet_version_id, s.name, sv.version'
          . ' FROM '
          . $xoopsDB->prefix('xf_snippet')
          . ' s,'
          . $xoopsDB->prefix('xf_snippet_version')
          . ' sv,'
          . $xoopsDB->prefix('xf_snippet_package_item')
          . ' spi'
          . ' WHERE s.snippet_id=sv.snippet_id'
          . ' AND sv.snippet_version_id=spi.snippet_version_id'
          . " AND spi.snippet_package_version_id='$snippet_package_version_id'";
$result = $xoopsDB->query($sql);
while (false !== ($row = $xoopsDB->fetchArray($result))) {
    $mypackage[] = $row;

    if (false !== ($key = array_search($row, $mysnippets, true))) {
        unset($mysnippets[$key]);
    }
}
if ($mysnippets) {
    echo '
   <FORM ACTION="' . XOOPS_URL . '/modules/xfsnippet/add_snippet_to_package.php" METHOD="POST">
    <INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
    <INPUT TYPE="HIDDEN" NAME="snippet_package_version_id" VALUE="' . $snippet_package_version_id . '">
    <INPUT TYPE="HIDDEN" NAME="suppress_nav" VALUE="' . $suppress_nav . '">';

    echo '
    <SELECT NAME="available[]" SIZE="10" MULTIPLE>';

    foreach ($mysnippets as $mysnippet) {
        echo '
     <OPTION VALUE="' . $mysnippet['snippet_version_id'] . '">' . $mysnippet['name'] . ' v' . $mysnippet['version'] . '</OPTION>';
    }

    echo '
    </SELECT>
    <BR>
  </TD>
 </TR>
 <TR>
  <TD>
    <INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="' . _XF_SNP_ADDSNIPPET . '">
   </FORM>
  </TD>
 </TR>
</TABLE>';
} else {
    echo '
 </TR>
 <TR>
  <TD>
  </TD>
 </TR>
</TABLE>
<B>' . _XF_SNP_NOSNIPPETSTOADD . '</B>
<BR>
<BR>
<HR>';
}
/**
 * Show the snippets in this package
 */
$result = $xoopsDB->query(
    'SELECT spi.snippet_version_id, sv.version, s.name '
    . 'FROM '
    . $xoopsDB->prefix('xf_snippet')
    . ' s,'
    . $xoopsDB->prefix('xf_snippet_version')
    . ' sv,'
    . $xoopsDB->prefix('xf_snippet_package_item')
    . ' spi '
    . 'WHERE s.snippet_id=sv.snippet_id '
    . 'AND sv.snippet_version_id=spi.snippet_version_id '
    . "AND spi.snippet_package_version_id='$snippet_package_version_id'"
);
$rows = $xoopsDB->getRowsNum($result);

if (!$result || $rows < 1) {
    echo $xoopsDB->error();

    echo '
<P>' . _XF_SNP_NOSNIPPETSINPACKAGE . '<P>';
} else {
    echo '
<BR>
<TABLE BORDER="0" WIDTH="100%">
 <TR>
  <TD COLSPAN="3">
  <B>' . _XF_SNP_SNIPPETSINPACKAGE . '</B>
  </TD>
 </TR>
 <TR>
  <TD>
  </TD>
  <TD>
   <B>' . _XF_SNP_NAME . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_VERSION . '</B>
  </TD>
 </TR>';

    for ($i = 0; $i < $rows; $i++) {
        echo '
 <TR CLASS="' . ($i % 2 > 0 ? 'even' : 'odd') . '">
  <TD ALIGN="MIDDLE">
    <A HREF="' . XOOPS_URL . '/modules/xfsnippet/delete.php?type=frompackage&snippet_version_id=' . unofficial_getDBResult($result, $i, 'snippet_version_id') . '&snippet_package_version_id=' . $snippet_package_version_id . '"><IMG SRC="' . XOOPS_URL . '/images/xf/trash.png" WIDTH="16" HEIGHT="16" BORDER="0"></A>
  </TD>
  <TD WIDTH="99%">
   ' . htmlspecialchars(unofficial_getDBResult($result, $i, 'name'), ENT_QUOTES | ENT_HTML5) . '
  </TD>
  <TD>
   ' . htmlspecialchars(unofficial_getDBResult($result, $i, 'version'), ENT_QUOTES | ENT_HTML5) . '
  </TD>
 </TR>';
    }

    //               $last_group = unofficial_getDBResult($result, $i, 'group_id');

    echo '
</TABLE>';
}

require_once XOOPS_ROOT_PATH . '/footer.php';

exit;
