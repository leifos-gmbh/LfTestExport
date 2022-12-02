<?php

class lfLPStatusXmlAuthor
{
    /**
     * @var lfLPStatusInfosFinder
     */
    private $finder;

    /**
     * @var lfLPStatusXmlPresenter
     */
    private $presenter;

    public function __construct(
        lfLPStatusInfosFinder $finder,
        lfLPStatusXmlPresenter $presenter
    ) {
        $this->finder = $finder;
        $this->presenter = $presenter;
    }

    public function writeHeader(
        int $ref_id,
        ilXmlWriter $writer
    ): void {
        $writer->xmlSetGenCmt(
            'Learning Progress Information for the Object with RefId ' . $ref_id
        );
        $writer->xmlHeader();
    }

    public function writeObjectLPStatus(
        int $ref_id,
        ilXmlWriter $writer
    ): void {
        $collection = $this->finder->getInfosCollection($ref_id);

        $object_infos = $collection->objectInfos();
        $this->writeObjectInfosStart($writer, $object_infos);

        foreach ($collection->allUserInfos() as $user_infos) {
            $this->writeUserInfos($writer, $user_infos);
        }

        $this->writeObjectInfosEnd($writer);
    }

    private function writeObjectInfosStart(
        ilXmlWriter $writer,
        lfLPStatusObjectInfos $object_infos
    ): void {
        $writer->xmlStartTag('ObjectLPStatus', [
            'RefId' => $object_infos->refId(),
            'ObjId' => $object_infos->objId(),
            'Type' => $object_infos->type(),
            'Online' => $object_infos->isOnline()
        ]);
        $writer->xmlElement('Title', null, $object_infos->title());
        $writer->xmlElement('Description', null, $object_infos->description());
        $writer->xmlStartTag('LPMode');
        $writer->xmlElement('Mode', null, $object_infos->LPMode());
        $writer->xmlElement('Info', null, $object_infos->LPModeInfo());
        $writer->xmlEndTag('LPMode');
        $writer->xmlStartTag('Users');
    }

    private function writeObjectInfosEnd(
        ilXmlWriter $writer
    ): void {
        $writer->xmlEndTag('Users');
        $writer->xmlEndTag('ObjectLPStatus');
    }

    private function writeUserInfos(
        ilXmlWriter $writer,
        lfLPStatusUserInfos $user_infos
    ): void {
        $personal = $user_infos->personal();
        $lp = $user_infos->learningProgress();

        $writer->xmlStartTag('UserLPStatus', [
            'UsrId' => $personal->usrId(),
            'ADLogin' => $personal->ADLogin(),
            'DisplayName' => $personal->login()
        ]);

        $writer->xmlElement('LPStatus', null, $this->presenter->formatStatus($lp->status()));
        $writer->xmlElement('Percentage', null, $this->presenter->formatPercentage($lp->percentage()));
        $writer->xmlElement('Mark', null, $lp->mark());
        $writer->xmlElement('Remark', null, $lp->remark());

        $writer->xmlElement('Personal', [
            'FirstName' => $personal->firstname(),
            'LastName' => $personal->lastname(),
            'E-Mail' => $personal->email()
        ]);

        $writer->xmlElement('Statistics', [
            'AccessCount' => $lp->accessCount(),
            'FirstAccess' => $this->presenter->formatDate($lp->firstAccess()),
            'LastAccess' => $this->presenter->formatDate($lp->lastAccess()),
            'LastStatusChange' => $this->presenter->formatDate($lp->lastStatusChange())
        ]);

        $writer->xmlEndTag('UserLPStatus');
    }
}
