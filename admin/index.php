<?php

require dirname(__DIR__, 3) . '/include/cp_header.php';

$fct = $_POST['fct'] ?? $_GET['fct'];

require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';

/*********************************************************/

/* Admin Authentication                                  */

/*********************************************************/

$admintest = 0;

if ($xoopsUser) {
    $xoopsModule = XoopsModule::getByDirname('xfsnippet');

    if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
        redirect_header(XOOPS_URL . '/', 3, _NOPERM);

        exit();
    }

    $admintest = 1;
} else {
    redirect_header(XOOPS_URL . '/', 3, _NOPERM);

    exit();
}

if (1 == $admintest) {
    xoops_cp_header();

    OpenTable();

    echo '<H4>General Configuration</H4>'

         . '<P>'

         . '<UL>'

         . "<LI><a href='" . XOOPS_URL . '/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=' . $xoopsModule->getVar('mid') . "'>" . _PREFERENCES . '</a>'

         . '</UL>'

         . '<H4><H4>Manage Snippets</H4>'

         . '<P>'

         . '<UL>'

         . "<LI><a href='" . XOOPS_URL . '/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=' . $xoopsModule->getVar('mid') . "'>Is coming...</a>"

         . '</UL>'

         . '<H4><H4>Snippet Utilities</H4>'

         . '<P>'

         . '<UL>'

         . "<LI><A HREF='main.php?op=EditTable&unit=script+type&table=xf_snippet_type&pk=type_id'>" . _XF_SNP_ADMENU1 . '</A>'

         . "<LI><A HREF='main.php?op=EditTable&unit=script+language&table=xf_snippet_language&pk=type_id'>" . _XF_SNP_ADMENU2 . '</A>'

         . "<LI><A HREF='main.php?op=EditTable&unit=script+category&table=xf_snippet_category&pk=type_id'>" . _XF_SNP_ADMENU3 . '</A>'

         . '</UL>';

    CloseTable();

    xoops_cp_footer();
}

function EditTable($unit, $table, $pk, $func, $id)
{
    site_admin_header();

    echo '<H4>Edit ' . ucwords($unit) . 's</H4>'

         . '<P>';

    $baseurl = 'admin.php?fct=utils&op=EditTable&table=' . $table . '&pk=' . $pk . '&unit=' . urlencode($unit);

    switch ($func) {
        case 'add':
        {
            admin_table_add($table, $unit, $pk, $baseurl);

            break;
        }

        case 'postadd':
        {
            admin_table_postadd($table, $unit, $pk, $baseurl);

            break;
        }

        case 'confirmdelete':
        {
            admin_table_confirmdelete($table, $unit, $pk, $id, $baseurl);

            break;
        }

        case 'delete':
        {
            admin_table_delete($table, $unit, $pk, $id, $baseurl);

            break;
        }

        case 'edit':
        {
            admin_table_edit($table, $unit, $pk, $id, $baseurl);

            break;
        }

        case 'postedit':
        {
            admin_table_postedit($table, $unit, $pk, $id, $baseurl);

            break;
        }
    }

    echo admin_table_show($table, $unit, $pk, $baseurl);

    site_admin_footer();
}
