<#1>
<?php

$query = "DELETE FROM ctrl_classfile WHERE plugin_path LIKE './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/LfTestExport';";
$res = $ilDB->manipulate($query);

?>
