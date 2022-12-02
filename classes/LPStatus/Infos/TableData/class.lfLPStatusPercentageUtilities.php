<?php

class lfLPStatusPercentageUtilities
{
    public function isAvailable(lfLPStatusObjectInfos $object_infos): bool
    {
        $supports_percentage = [
            ilLPObjSettings::LP_MODE_TLT,
            ilLPObjSettings::LP_MODE_VISITS,
            ilLPObjSettings::LP_MODE_SCORM,
            ilLPObjSettings::LP_MODE_LTI_OUTCOME,
            ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
            ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
            ilLPObjSettings::LP_MODE_CMIX_PASSED,
            ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
            ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
            ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED,
            ilLPObjSettings::LP_MODE_VISITED_PAGES,
            ilLPObjSettings::LP_MODE_TEST_PASSED
        ];

        if (in_array($object_infos->LPModeId(), $supports_percentage)) {
            return true;
        }
        return false;
    }
}