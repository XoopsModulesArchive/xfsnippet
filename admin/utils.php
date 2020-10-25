<?php

require dirname(__DIR__, 3) . '/include/cp_header.php';

function UtilsMain()
{
    //xoops_cp_header();

    echo '<H4>Site Utilities</H4>'

         . '<P>'

         . '<UL>'

         . "<LI><A HREF='admin.php?fct=utils&op=EditTable&unit=file+type&table=xf_frs_filetype&pk=type_id'>Add, Delete, or Edit File Types</A>"

         . "<LI><A HREF='admin.php?fct=utils&op=EditTable&unit=processor&table=xf_frs_processor&pk=processor_id'>Add, Delete, or Edit Processors</A>"

         . "<LI><A HREF='admin.php?fct=utils&op=EditTable&unit=script+type&table=xf_snippet_type&pk=type_id'>Add, Delete, or Edit Snippet Types</A>"

         . "<LI><A HREF='admin.php?fct=utils&op=EditTable&unit=script+language&table=xf_snippet_language&pk=type_id'>Add, Delete, or Edit Snippet Languages</A>"

         . "<LI><A HREF='admin.php?fct=utils&op=EditTable&unit=script+category&table=xf_snippet_category&pk=type_id'>Add, Delete, or Edit Snippet Categories</A>"

         . '</UL>';
}

function EditTable($unit, $table, $pk, $func, $id)
{
    xoops_cp_header();

    OpenTable();

    echo '<H4>Edit ' . ucwords($unit) . 's</H4>'

         . '<P>';

    $baseurl = 'main.php?op=EditTable&table=' . $table . '&pk=' . $pk . '&unit=' . urlencode($unit);

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

    CloseTable();

    xoops_cp_footer();
}
