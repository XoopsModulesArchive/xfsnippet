<?php
/**
 * Misc HTML functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: html.php,v 1.1.1.1 2002/11/07 17:26:19 pjones Exp $
 * @param mixed $vals
 * @param mixed $select_name
 * @param mixed $checked_val
 * @param mixed $samevals
 */

/**
 * html_build_select_box_from_array() - Takes one array, with the first array being the "id"
 * or value and the array being the text you want displayed.
 *
 * @param string $vals        The name you want assigned to this form element
 * @param string $select_name The value of the item that should be checked
 * @param string $checked_val
 * @param int    $samevals
 * @return string
 */
function html_build_select_box_from_array($vals, $select_name, $checked_val = 'xzxz', $samevals = 0)
{
    $return = '
		<SELECT NAME="' . $select_name . '">';

    $rows = count($vals);

    for ($i = 0; $i < $rows; $i++) {
        if ($samevals) {
            $return .= "\n\t\t<OPTION VALUE=\"" . $vals[$i] . '"';

            if ($vals[$i] == $checked_val) {
                $return .= ' SELECTED';
            }
        } else {
            $return .= "\n\t\t<OPTION VALUE=\"" . $i . '"';

            if ($i == $checked_val) {
                $return .= ' SELECTED';
            }
        }

        $return .= '>' . $vals[$i] . '</OPTION>';
    }

    $return .= '
		</SELECT>';

    return $return;
}

/**
 * html_build_select_box_from_arrays() - Takes two arrays, with the first array being the "id" or value and the other
 * array being the text you want displayed.
 *
 * The infamous '100 row' has to do with the SQL Table joins done throughout all this code.
 * There must be a related row in users, categories, et    , and by default that
 * row is 100, so almost every pop-up box has 100 as the default
 * Most tables in the database should therefore have a row with an id of 100 in it so that joins are successful
 *
 * @param mixed $vals
 * @param mixed $texts
 * @param mixed $select_name
 * @param mixed $checked_val
 * @param mixed $show_100
 * @param mixed $text_100
 * @return string
 */
function html_build_select_box_from_arrays($vals, $texts, $select_name, $checked_val = 'xzxz', $show_100 = true, $text_100 = _XF_G_NONE)
{
    $return = '
		<SELECT NAME="' . $select_name . '">';

    //we don't always want the default 100 row shown

    if ($show_100) {
        $return .= '
		<OPTION VALUE="100">' . $text_100 . '</OPTION>';
    }

    $rows = count($vals);

    if (count($texts) != $rows) {
        $return .= 'ERROR - uneven row counts';
    }

    for ($i = 0; $i < $rows; $i++) {
        //  uggh - sorry - don't show the 100 row

        //  if it was shown above, otherwise do show it

        if (('100' != $vals[$i]) || ('100' == $vals[$i] && !$show_100)) {
            $return .= '
				<OPTION VALUE="' . $vals[$i] . '"';

            if ($vals[$i] == $checked_val) {
                $checked_found = true;

                $return .= ' SELECTED';
            }

            $return .= '>' . $texts[$i] . '</OPTION>';
        }
    }

    //

    //	If the passed in "checked value" was never "SELECTED"

    //	we want to preserve that value UNLESS that value was 'xzxz', the default value

    //

    if (!$checked_found && 'xzxz' != $checked_val && $checked_val && 100 != $checked_val) {
        $return .= '
		<OPTION VALUE="' . $checked_val . '" SELECTED>' . _XF_G_NOCHANGE . '</OPTION>';
    }

    $return .= '
		</SELECT>';

    return $return;
}

/**
 * html_build_select_box() - Takes a result set, with the first column being the "id" or value and
 * the second column being the text you want displayed.
 *
 * @param mixed $result
 * @param mixed $name
 * @param mixed $checked_val
 * @param mixed $show_100
 * @param mixed $text_100
 * @return string
 */
function html_build_select_box($result, $name, $checked_val = 'xzxz', $show_100 = true, $text_100 = _XF_G_NONE)
{
    return html_build_select_box_from_arrays(util_result_column_to_array($result, 0), util_result_column_to_array($result, 1), $name, $checked_val, $show_100, $text_100);
}

/**
 * html_build_multiple_select_box() - Takes a result set, with the first column being the "id" or value
 * and the second column being the text you want displayed.
 *
 * @param mixed $result
 * @param mixed $name
 * @param mixed $checked_array
 * @param mixed $size
 * @param mixed $show_100
 * @return string
 */
function html_build_multiple_select_box($result, $name, $checked_array, $size = '8', $show_100 = true)
{
    global $xoopsDB;

    $checked_count = count($checked_array);

    $return .= '
		<SELECT NAME="' . $name . '" MULTIPLE SIZE="' . $size . '">';

    if ($show_100) {
        /*
            Put in the default NONE box
        */

        $return .= '
		<OPTION VALUE="100"';

        for ($j = 0; $j < $checked_count; $j++) {
            if ('100' == $checked_array[$j]) {
                $return .= ' SELECTED';
            }
        }

        $return .= '>' . _XF_G_NONE . '</OPTION>';
    }

    $rows = $xoopsDB->getRowsNum($result);

    for ($i = 0; $i < $rows; $i++) {
        if (('100' != unofficial_getDBResult($result, $i, 0)) || ('100' == unofficial_getDBResult($result, $i, 0) && !$show_100)) {
            $return .= '
				<OPTION VALUE="' . unofficial_getDBResult($result, $i, 0) . '"';

            /*
                Determine if it's checked
            */

            $val = unofficial_getDBResult($result, $i, 0);

            for ($j = 0; $j < $checked_count; $j++) {
                if ($val == $checked_array[$j]) {
                    $return .= ' SELECTED';
                }
            }

            $return .= '>' . $val . '-' . mb_substr(unofficial_getDBResult($result, $i, 1), 0, 35) . '</OPTION>';
        }
    }

    $return .= '
		</SELECT>';

    return $return;
}

/**
 *    html_build_checkbox() - Render checkbox control
 *
 * @param mixed $name
 * @param mixed $value
 * @param mixed $checked
 * @return html code for checkbox control
 */
function html_build_checkbox($name, $value, $checked)
{
    return '<input type="checkbox" name="' . $name . '"' . ' value="' . $value . '"' . ($checked ? 'checked' : '') . '>';
}

/**
 * html_buildcheckboxarray() - Build an HTML checkbox array.
 *
 * @param mixed $options
 * @param mixed $name
 * @param mixed $checked_array
 */
function html_buildcheckboxarray($options, $name, $checked_array)
{
    $option_count = count($options);

    $checked_count = count($checked_array);

    for ($i = 1; $i <= $option_count; $i++) {
        echo '
			<BR><INPUT type="checkbox" name="' . $name . '" value="' . $i . '"';

        for ($j = 0; $j < $checked_count; $j++) {
            if ($i == $checked_array[$j]) {
                echo ' CHECKED';
            }
        }

        echo '> ' . $options[$i];
    }
}
