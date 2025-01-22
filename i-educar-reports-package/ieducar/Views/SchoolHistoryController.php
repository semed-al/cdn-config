<?php

class SchoolHistoryController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999200;

    /**
     * @var string
     */
    protected $_titulo = 'Histórico Escolar';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão do histórico escolar', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->simpleSearchAluno(null);

        $this->inputsHelper()->checkbox('lote', ['label' => 'Emitir em lote?']);

        // $this->inputsHelper()->checkbox('emitir_carga_horaria_frequentada', ['label' => 'Emitir Carga horária frequentada']);

        $resources = [
            1 => 'Série/Anos',
            2 => 'EJA'
        ];
        $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];
        $this->inputsHelper()->select('modelo', $options);

        $this->inputsHelper()->integer('ano_ini', ['placeholder' => '', 'required' => false, 'label' => 'Ano início', 'max_length' => 4, 'size' => 4]);
        $this->inputsHelper()->integer('ano_fim', ['placeholder' => '','required' => false, 'label' => 'Ano final', 'max_length' => 4, 'size' => 4]);

        // $helperOptions = ['objectName' => 'cursoaluno'];
        // $options = ['label' => 'Cursos do aluno', 'size' => 120, 'required' => false, 'placeholder' => 'Todas', 'options' => ['value' => null]];
        // $this->inputsHelper()->multipleSearchCursoAluno('', $options, $helperOptions);
        
        // $this->inputsHelper()->checkbox('apenas_ultimo_registro', ['label' => 'Emitir informações do ano letivo apenas do último registro?']);
        $this->campoOculto('sequencial', $this->sequencial);

        $this->inputsHelper()->text('alterar_nome_diretor', ['label' => 'Alterar nome do(a) diretor(a)', 'value' => false, 'required' => false]);
        $this->inputsHelper()->text('alterar_nome_secretario', ['label' => 'Alterar nome do(a) secretário(a) escolar', 'value' => false, 'required' => false]);

        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $modelo = (int) $this->getRequest()->modelo;
        
        $this->report->addArg('dominio', $_SERVER['HTTP_HOST']);
        $this->report->addArg('alterar_nome_diretor', $this->getRequest()->alterar_nome_diretor);
        $this->report->addArg('alterar_nome_secretario', $this->getRequest()->alterar_nome_secretario);
        
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('aluno', (int) $this->getRequest()->aluno_id);
        $this->report->addArg('modelo', $modelo);
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('portaria_aprovacao_pontos', (string) $GLOBALS['coreExt']['Config']->report->portaria_aprovacao_pontos);
        $this->report->addArg('nao_emitir_reprovado', (bool) $this->getRequest()->nao_emitir_reprovado);
        $this->report->addArg('mostrar_msg', (bool) $this->getRequest()->mostrar_msg);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('emitir_carga_horaria_frequentada', (bool) $this->getRequest()->emitir_carga_horaria_frequentada);
        $this->report->addArg('ano_ini', ($this->getRequest()->ano_ini == '' ? 0 : (int)$this->getRequest()->ano_ini));
        $this->report->addArg('ano_fim', ($this->getRequest()->ano_fim == '' ? 0 : (int)$this->getRequest()->ano_fim));
        $cursoaluno = implode(',', array_filter($this->getRequest()->cursoaluno ?? []));
        $this->report->addArg('cursoaluno', trim($cursoaluno) == '' ? 0 : $cursoaluno);
        $this->report->addArg('apenas_ultimo_registro', (bool)$this->getRequest()->apenas_ultimo_registro);
        $this->report->addArg('sequencial', (int)$this->getRequest()->sequencial);
        
    }

    /**
     * @return SchoolHistoryReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new SchoolHistoryReport();
    }
}
