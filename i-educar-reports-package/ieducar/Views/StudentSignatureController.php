<?php

class StudentSignatureController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 19998701;

    /**
     * @var string
     */
    protected $_titulo = 'Lista de alunos para assinatura';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Lista de alunos para assinatura dos pais', [
            '/intranet/educar_index.php' => 'Escola'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic(['curso', 'serie', 'turma'], ['required' => false]);

        $resources = [
            10 => 'Todas',
            9 => 'Exceto Transferidos/Abandono',
            1 => 'Aprovado',
            2 => 'Reprovado',
            3 => 'Cursando',
            4 => 'Transferido',
            5 => 'Reclassificado',
            6 => 'Abandono',
            7 => 'Em exame',
            12 => 'Aprovado com dependência'
        ];

        $options = [
            'label' => 'Situação',
            'resources' => $resources,
            'value' => 9,
            'required' => false
        ];

        $this->inputsHelper()->select('situacao', $options);
        $this->inputsHelper()->text('titulo', ['label' => 'Novo título', 'required' => false, 'size' => 30]);
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
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);
        $this->report->addArg('definir_titulo', true);
        $this->report->addArg('titulo', $this->getRequest()->titulo);
        $this->report->addArg('type', 'student');
    }

    /**
     * @return 
     *
     * @throws Exception
     */
    public function report()
    {
        return new ParentSignatureReport();
    }
}
