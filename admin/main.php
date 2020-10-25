<?php

require_once 'dbtable.php';

require_once 'utils.php';

switch ($op) {
    case 'EditTable':

        EditTable($unit, $table, $pk, $func, $id);

        break;
    default:

        UtilsMain();

        break;
}
