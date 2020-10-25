<?php

function addMsg($msg)
{
    global $feedback;

    if (mb_strlen($feedback) < 1) {
        $feedback = $msg;
    } else {
        $feedback = '<BR>' . $msg;
    }
}

function snippet_header($title)
{
    global $feedback;

    $header = '
<H4>' . $title . '</H4>
<P>
<B>
<A HREF="' . XOOPS_URL . '/modules/xfsnippet/"> ' . _XF_SNP_FINDSNIPPET . ' </A>
|
<A HREF="' . XOOPS_URL . '/modules/xfsnippet/submit.php"> ' . _XF_SNP_SUBMITNEWSNIPPET . ' </A>
|
<A HREF="' . XOOPS_URL . '/modules/xfsnippet/package.php"> ' . _XF_SNP_CREATEASNIPPETPACKAGE . ' </A>
</B>
<br>
<br>';

    if (mb_strlen($feedback)) {
        echo '
<FONT COLOR="red">' . $feedback . '</FONT>
<br>';
    }

    return $header;
}

function snippet_show_package_snippets($id, $package_version)
{
    global $xoopsDB, $myts, $content;

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

    return $content;
}

function snippet_show_package_details($snippet_id)
{
    global $xoopsDB, $myts, $content;

    $sql = 'SELECT sp.name as name,sp.description as description,sl.name as language,sc.name as category'
              . ' FROM '
              . $xoopsDB->prefix('xf_snippet_package')
              . ' as sp,'
              . $xoopsDB->prefix('xf_snippet_language')
              . ' as sl,'
              . $xoopsDB->prefix('xf_snippet_category')
              . ' as sc '
              . ' WHERE snippet_package_id='
              . $snippet_id
              . ' AND sl.type_id=sp.language'
              . ' AND sc.type_id=sp.category';

    $result = $xoopsDB->query($sql);

    if ($result) {
        [$name, $description, $language, $category] = $xoopsDB->fetchRow($result);
    }

    if (!$name) {
        return false;
    }

    $content .= '
<P>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="0">
        <TR class="head">
  <TD COLSPAN="2">
   ' . _XF_SNP_DESCRIPTION . '
  </TD>
 </TR>
 <TR class="odd">
  <TD COLSPAN="2">
   ' . htmlspecialchars($description, ENT_QUOTES | ENT_HTML5) . '
  </TD>
 </TR>
 <TR>
  <TD COLSPAN="2">
   &nbsp;
  </TD>
 </TR>
 <TR CLASS="head">
  <TD>
   <B>' . _XF_SNP_CATEGORY . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_LANGUAGE . '</B>
  </TD>
 </TR>
 <TR class="odd">
  <TD>
   ' . $category . '
  </TD>
  <TD>
   ' . $language . '
  </TD>
 </TR>
</TABLE>';

    return $content;
}

function snippet_show_snippet_header($snippet_id)
{
    global $xoopsDB, $myts, $content;

    $sql = 'SELECT s.name,s.description,s.license,st.type_id,st.name,sl.name,sc.name'
                . ' FROM '
                . $xoopsDB->prefix('xf_snippet')
                . ' as s, '
                . $xoopsDB->prefix('xf_snippet_language')
                . ' sl, '
                . $xoopsDB->prefix('xf_snippet_category')
                . ' sc, '
                . $xoopsDB->prefix('xf_snippet_type')
                . ' st'
                . ' WHERE s.snippet_id='
                . $snippet_id
                . ' AND st.type_id=s.type'
                . ' AND sl.type_id=s.language'
                . ' AND sc.type_id=s.category';

    $sql_type = $xoopsDB->query($sql);

    $myra = $xoopsDB->fetchRow($sql_type);

    [$name, $description, $license, $type, $typename, $language, $category] = $myra;

    if (!$name) {
        return false;
    }

    $content .= '
<P>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="0">
 <TR CLASS="head2">
  <TD COLSPAN="4" ALIGN=CENTER><B>' . $name . '</B></TD>
 </TR>
 <TR class="even">
  <TD COLSPAN="4">
   <B>' . _XF_SNP_DESCRIPTION . '</B>
  </TD>
 </TR>
 <TR class="odd">
  <TD COLSPAN="4">
   ' . htmlspecialchars($description, ENT_QUOTES | ENT_HTML5) . '
  </TD>
 </TR>
 <TR>
  <TD COLSPAN="4">
   &nbsp;
  </TD>
</TABLE>';

    return $content;
}

function snippet_show_snippet_details($snippet_id)
{
    global $SCRIPT_LICENSE, $xoopsDB, $myts, $content;

    $sql = 'SELECT s.name,s.description,s.license,st.type_id,st.name,sl.name,sc.name'
                . ' FROM '
                . $xoopsDB->prefix('xf_snippet')
                . ' s, '
                . $xoopsDB->prefix('xf_snippet_language')
                . ' sl, '
                . $xoopsDB->prefix('xf_snippet_category')
                . ' sc, '
                . $xoopsDB->prefix('xf_snippet_type')
                . ' st'
                . " WHERE s.snippet_id='$snippet_id'"
                . ' AND st.type_id=s.type'
                . ' AND sl.type_id=s.language'
                . ' AND sc.type_id=s.category';

    $sql_type = $xoopsDB->query($sql);

    $myra = $xoopsDB->fetchRow($sql_type);

    [$name, $description, $license, $type, $typename, $language, $category] = $myra;

    if (!$name) {
        return false;
    }

    $content = '     
<P>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="0">
 <TR CLASS="head">
  <TD COLSPAN="4" ALIGN=CENTER><B>' . $name . '</B></TD>
 </TR>
 <TR CLASS="even">
  <TD COLSPAN="4">
   <B>' . _XF_SNP_DESCRIPTION . '</B>
  </TD>
 </TR>
 <TR CLASS="odd">
  <TD COLSPAN="4">
   ' . htmlspecialchars($description, ENT_QUOTES | ENT_HTML5) . '
  </TD>
 </TR>
 <TR>
  <TD COLSPAN="4">
   &nbsp;
  </TD>
 </TR>
 <TR CLASS="even">
  <TD>
   <B>' . _XF_SNP_TYPE . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_CATEGORY . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_LICENSE . '</B>
  </TD>
  <TD>
   <B>' . _XF_SNP_LANGUAGE . '</B>
  </TD>
 </TR>
 <TR CLASS="odd">
  <TD>
  ' . $typename . '
  </TD>
  <TD>
   ' . $category . '
  </TD>
  <TD>
   ' . $SCRIPT_LICENSE[$license] . '
  </TD>
  <TD>
   ' . $language . '
  </TD>
 </TR>
</TABLE>';

    return $content;
}

function snippet_show_snippet($id, $version)
{
    global $xoopsDB, $myts, $contents;

    /*
        show the latest version of this snippet's code
    */

    $result = $xoopsDB->query(
        'SELECT v.code,v.version' . ' FROM ' . $xoopsDB->prefix('xf_snippet_version') . ' as v' . ', ' . $xoopsDB->prefix('xf_snippet') . ' as s' . ' WHERE s.snippet_id=v.snippet_id' . ' AND s.snippet_id=' . $id . ' AND v.snippet_version_id=' . $version
    );

    if (preg_match("#\[php\](.*?)\[/php\]#si", unofficial_getDBResult($result, 0, 'code'))) {
        $code = bbencode_highlight_php(unofficial_getDBResult($result, 0, 'code'));
    } else {
        $code = $myts->xoopsCodeDecode(unofficial_getDBResult($result, 0, 'code'));

        $code = $myts->nl2Br($code);
    }

    $contents = '<p><B>' . _XF_SNP_VERSION . ': ' . htmlspecialchars(unofficial_getDBResult($result, 0, 'version'), ENT_QUOTES | ENT_HTML5) . '</B>';

    $contents .= ' - <a href="' . XOOPS_URL . '/modules/xfsnippet/download.php?id=' . $id . '&version=' . $version . '">Download</a>';

    $contents .= '<BR><DIV CLASS="xoopsCode">' . $code . '</DIV>';

    return $contents;
}

/**
 * MySQL database connection/querying layer
 * @param mixed $qhandle
 * @param mixed $row
 * @param mixed $field
 * @return string
 * @return string
 */
function unofficial_getDBResult($qhandle, $row, $field)
{
    return @mysql_result($qhandle, $row, $field);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 * @param mixed $qhandle
 */
function unofficial_getAffectedRows($qhandle)
{
    return @$GLOBALS['xoopsDB']->getAffectedRows();
}

function unofficial_ResetResult($qhandle, $row = 0)
{
    return mysql_data_seek($qhandle, $row);
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 * @param mixed $lhandle
 */
function unofficial_getNumFields($lhandle)
{
    return @mysql_numfields($lhandle);
}

/**
 *  db_fieldname() - Returns the number of rows changed in the last query
 *
 * @param mixed $lhandle
 * @param mixed $fnumber
 */
function unofficial_getFieldName($lhandle, $fnumber)
{
    return @mysql_fieldname($lhandle, $fnumber);
}

function util_result_column_to_array($result, $col = 0)
{
    global $xoopsDB;

    /*
        Takes a result set and turns the optional column into
        an array
    */

    $rows = $xoopsDB->getRowsNum($result);

    if ($rows > 0) {
        $arr = [];

        for ($i = 0; $i < $rows; $i++) {
            $arr[$i] = unofficial_getDBResult($result, $i, $col);
        }
    } else {
        $arr = [];
    }

    return $arr;
}

/**
 * util_result_columns_to_assoc() - Takes a result set and turns the column pair into an associative array
 *
 * @param mixed $result
 * @param mixed $col_key
 * @param mixed $col_val
 * @return array
 */
function util_result_columns_to_assoc($result, $col_key = 0, $col_val = 1)
{
    global $xoopsDB;

    $rows = $xoopsDB->getRowsNum($result);

    if ($rows > 0) {
        $arr = [];

        for ($i = 0; $i < $rows; $i++) {
            $arr[unofficial_getDBResult($result, $i, $col_key)] = unofficial_getDBResult($result, $i, $col_val);
        }
    } else {
        $arr = [];
    }

    return $arr;
}

function bbencode_highlight_php($code)
{
    //$code = str_replace("\\\"","\"",$code);

    $matches = [];

    $match_count = preg_match_all("#\[php\](.*?)\[/php\]#si", $code, $matches);

    for ($i = 0; $i < $match_count; $i++) {
        $before_replace = $matches[1][$i];

        $after_replace = trim($matches[1][$i]);

        $str_to_match = '[php]' . $before_replace . '[/php]';

        $replacement = '';

        $after_replace = str_replace('&lt;', '<', $after_replace);

        $after_replace = str_replace('&gt;', '>', $after_replace);

        $after_replace = str_replace('&amp;', '&', $after_replace);

        $added = false;

        if (preg_match('/^<\?.*?\?>$/si', $after_replace) <= 0) {
            $after_replace = '<? ' . wbb_trim($after_replace) . ' ?>';

            $added = true;
        }

        if (strcmp('4.2.0', phpversion()) > 0) {
            ob_start();

            highlight_string($after_replace);

            $after_replace = ob_get_contents();

            ob_end_clean();
        } else {
            $after_replace = highlight_string($after_replace, true);
        }

        if (true === $added) {
            $after_replace = str_replace('<font color="#0000BB">&lt;?php <br>', '<font color="#0000BB">', $after_replace);

            $after_replace = str_replace('<font color="#0000BB"><br>?&gt;</font>', '', $after_replace);
        }

        $after_replace = preg_replace('/<font color="(.*?)">/si', '<span style="color: \\1;">', $after_replace);

        $after_replace = str_replace('</font>', '</span>', $after_replace);

        $after_replace = str_replace('\n', '', $after_replace);

        $replacement .= $after_replace;

        $code = str_replace($str_to_match, $replacement, $code);

        if (1 == $linenumbers) {
            $linenumbers = makeLineNumbers($code);
        } else {
            $linenumbers = '';
        }

        //$buffer = str_replace("<code>", "", $buffer);
        //$buffer = str_replace("</code>", "", $buffer);

        /* if($phptags==1) {
          if(version_compare($phpversion, "4.3.0")==-1) $buffer=preg_replace("/([^\\2]*)(&lt;\?&nbsp;)(.*)(&nbsp;.*\?&gt;)([^\\4]*)/si","\\1\\3\\5",$buffer);
          else $buffer=preg_replace("/([^\\2]*)(&lt;\? )(.*)( .*\?&gt;)([^\\4]*)/si","\\1\\3\\5",$buffer);
         }
         $buffer=preg_replace("/<font color=\"([^\"]*)\">/i","<span style=\"color: \\1\">",str_replace("</font>","</span>",$buffer));
         if($phptags==1 && version_compare($phpversion, "4.3.0")!=-1) $buffer=str_replace("<font</span>","",$buffer);
         $buffer=str_replace("{","&#123;",$buffer);
         $buffer=str_replace("}","&#125;",$buffer);
         $buffer=str_replace("\n","",$buffer);
         $buffer=str_replace("<br>","\n",$buffer);

         $linecount = substr_count($buffer, "\n") + 1;
         $height = ($style['smallfontsize']+3)*$linecount + 50;

        if($linenumbers==1) $linenumbers=makeLineNumbers($code);
         else $linenumbers="";


              } */
        //$height = ($style['smallfontsize']+3)*$linecount + 50;
    }

    return $code;
}

function updaterating($sel_id)
{
    global $xoopsDB;

    $query = 'select rating FROM ' . $xoopsDB->prefix('xf_snippet_votedata') . ' WHERE lid = ' . $sel_id . '';

    //echo $query;

    $voteresult = $xoopsDB->query($query);

    $votesDB = $xoopsDB->getRowsNum($voteresult);

    $totalrating = 0;

    while (list($rating) = $xoopsDB->fetchRow($voteresult)) {
        $totalrating += $rating;
    }

    $finalrating = $totalrating / $votesDB;

    $finalrating = number_format($finalrating, 4);

    $query = 'UPDATE ' . $xoopsDB->prefix('xf_snippet') . " SET rating=$finalrating, votes=$votesDB WHERE snippet_id = $sel_id";

    //echo $query;

    $xoopsDB->query($query) || exit();
}

function makeLineNumbers($code, $split = "\n")
{
    $lines = explode($split, $code);

    $linenumbers = '';

    for ($i = 0, $iMax = count($lines); $i < $iMax; $i++) {
        $linenumbers .= ($i + 1) . ":\n";
    }

    return $linenumbers;
}

/** htmlconverter function *
 * @param $text
 * @return string
 */
function htmlconverter($text)
{
    //static $charset;

    global $phpversion;

    $charset = 'iso-8859-1';

    if (version_compare($phpversion, '4.3.0') >= 0
        && ('iso-8859-1' == $charset || 'iso-8859-15' == $charset || 'utf-8' == $charset || 'cp1252' == $charset || 'windows-1252' == $charset || 'koi8-r' == $charset || 'big5' == $charset || 'gb2312' == $charset || 'big5-hkscs' == $charset
            || 'shift_jis'
               == $charset
            || 'euc-jp' == $charset)) {
        return @htmlentities($text, ENT_COMPAT, $charset);
    } elseif ('iso-8859-1' == $charset || 'windows-1252' == $charset) {
        return htmlentities($text, ENT_QUOTES | ENT_HTML5);
    }
  

    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
}

/** own trim function for wbb *
 * @param $text
 * @return string
 */
function wbb_trim($text)
{
    if ('' != $text) {
        // removing whitespace may not work with some charsets (like utf-8,gb2312,etc)
        //global $removewhitespace;
        //if($removewhitespace==1) {
        $text = str_replace(chr(160), ' ', $text); // remove alt + 0160
        $text = str_replace(chr(173), ' ', $text); // remove alt + 0173
        $text = str_replace(chr(240), ' ', $text); // remove alt + 0240
        $text = str_replace("\0", ' ', $text); // remove whitespace
        //}
        $text = trim($text);
    }

    return $text;
}
