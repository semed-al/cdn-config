<?php

class ReportConceptualCardController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 19992022;

    /**
     * @var string
     */
    protected $_titulo = 'Conceitual';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->acao_enviar = 'printReport()';

        $this->breadcrumb('Conceitual', [
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
            'turma',
        ]);

        // $this->inputsHelper()->select('orientacao', [
        //     'label' => 'Orientação da página',
        //     'resources' => [
        //         1 => 'Retrato',
        //         2 => 'Paisagem'
        //     ],
        //     'required' => false,
        //     'value' => 1
        // ]);

        $this->inputsHelper()->dynamic('matricula', ['required' => false]);

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

        $options = [
            'label' => 'Situação do aluno',
            'resources' => $resources,
            'value' => 10
        ];

        $this->inputsHelper()->select('situacao_matricula', $options);
        
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
        $this->report->addArg('situacao_matricula', (int) $this->getRequest()->situacao_matricula);
        $this->report->addArg('orientacao', (int) $this->getRequest()->orientacao ?: 1);
        $this->report->addArg('alunos_diferenciados', (int) ($this->getRequest()->alunos_diferenciados ?: 0));

        if (is_null($this->getRequest()->ref_cod_matricula)) {
            $this->report->addArg('matricula', 0);
        } else {
            $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
        }
        $this->report->addArg('tipo_nota', 2);
    }

    /**
     * @return ReportConceptualCardReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ReportConceptualCardReport();
    }
}