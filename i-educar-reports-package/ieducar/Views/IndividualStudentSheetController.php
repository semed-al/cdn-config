<?php

class IndividualStudentSheetController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 19992041;

    /**
     * @var string
     */
    protected $_titulo = 'Ficha individual do aluno';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Ficha individual do aluno', [
            '/intranet/educar_index.php' => 'Escola'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic([
            'ano',
            'instituicao',
            'escola',
            'curso',
            'serie',
            'turma'
        ]);

        $this->inputsHelper()->dynamic('matricula', ['required' => true]);

        $resources = [
            1 => 'Aprovado',
            2 => 'Reprovado',
            14 => 'Reprovado por falta',
            3 => 'Cursando',
            4 => 'Transferido',
            5 => 'Reclassificado',
            6 => 'Abandono',
            7 => 'Em exame',
            9 => 'Exceto Transferidos/Abandono',
            10 => 'Todas',
            12 => 'Aprovado com dependência',
            16 => 'Aprovado após exame'
        ];

        // $options = [
        //     'label' => 'Situação do aluno',
        //     'resources' => $resources,
        //     'value' => 10
        // ];

        // $this->inputsHelper()->select('situacao_matricula', $options);

        $this->inputsHelper()->text('data', [
            'label' => 'Data de encerramento',
            'value' => '',
            'placeholder' => 'DD/MM/AAAA',
            'size' => 40,
            'required' => true
        ]);

        $this->inputsHelper()->text('alterar_nome_diretor', ['label' => 'Alterar nome do(a) diretor(a)', 'value' => false, 'required' => false]);
        $this->inputsHelper()->text('alterar_nome_secretario', ['label' => 'Alterar nome do(a) secretário(a) escolar', 'value' => false, 'required' => false]);
        
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('dominio', $_SERVER['HTTP_HOST']);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao_matricula', (int) 10);

        $months = array (1=>'Janeiro',2=>'Fevereiro',3=>'Marco',4=>'Abril',5=>'Maio',6=>'Junho',
        7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro');
        $data_part = explode('/', $this->getRequest()->data);
        $this->report->addArg('data_dia', (int) $data_part[0]);
        $this->report->addArg('data_mes', $months[(int) $data_part[1]]);
        $this->report->addArg('data_ano', (int) $data_part[2]);

        if (is_null($this->getRequest()->ref_cod_matricula)) {
            $this->report->addArg('matricula', 0);
        } else {
            $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
        }

        $conceitoAnual = getenv('ANNUAL_CONCEPT') ?: 0;
        $this->report->addArg('conceito_anual', (int) $conceitoAnual);
        
        $this->report->addArg('alterar_nome_diretor', $this->getRequest()->alterar_nome_diretor);
        $this->report->addArg('alterar_nome_secretario', $this->getRequest()->alterar_nome_secretario);
    }

    /**
     * @return ReportCardReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new IndividualStudentSheetReport();
    }
}
