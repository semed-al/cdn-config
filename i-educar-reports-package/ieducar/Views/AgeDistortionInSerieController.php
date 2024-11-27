<?php

class AgeDistortionInSerieController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999840;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de distorção de idade na série';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de distorção de idade na série', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => true]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('situacaoMatricula', ['value' => 9]);
        $this->campoLista('modelo', 'Modelo', [
            'regular' => 'Ensino regular',
            'geral' => 'Geral'
        ], $this->modelo);
        // $this->inputsHelper()->checkbox('escala_cinza', ['label' => 'Imprimir os gr&aacute;ficos em escala de cinza?']);
        $this->inputsHelper()->date('data_inicial', [
            'placeholder' => '',
            'label' => 'Data inicial',
            'value' => date('01/01/Y'),
            'required' => false
        ]);
        $this->inputsHelper()->date('data_final', [
            'placeholder' => '',
            'label' => 'Data final',
            'value' => date('t/m/Y'),
            'required' => false
        ]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('modelo', $this->getRequest()->modelo);
        $this->report->addArg('escala_cinza', (bool) $this->getRequest()->escala_cinza);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
    }

    /**
     * @return AgeDistortionInSerieReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new AgeDistortionInSerieReport();
    }
}
