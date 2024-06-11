<?php

use iEducar\Reports\JsonDataSource;

class ReportConceptualCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, DescriptiveOpinionsTrait {
        ReportCardTrait::query as QueryReportCard;
    }

    public function templateName()
    {
        $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();
        $template = $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL];
        
        return $template;
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    public function getJsonData()
    {
        $template = $this->templateName();

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->getQueryByTemplate()[$template]),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }

    private function getQueryByTemplate()
    {
        $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();

        return [
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL] => $this->QueryReportCard()
        ];
    }
}