<?php

class FinalResultController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999608;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório do Resultado Final';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão do resultado final', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);

        // $options = [
        //     'label' => 'Modelo',
        //     'resources' => [
        //         1 => 'Numérico',
        //     ],
        //     'required' => false,
        //     'value' => 1
        // ];

        // $this->inputsHelper()->select('modelo', $options);

        $options = [
            'label' => 'Orientação',
            'resources' => [
                'retrato' => 'Retrato',
                'paisagem' => 'Paisagem'
            ],
            'required' => false,
            'value' => 1
        ];

        $this->inputsHelper()->select('orientacao', $options);

        if ($GLOBALS['coreExt']['Config']->app->matricula->dependencia == 1) {
            $options = [
                'label' => 'Alunos com dependência',
                'resources' => [
                    0 => 'Todos',
                    1 => 'Somente alunos com dependência',
                    2 => 'Não exibir alunos com dependência'
                ],
                'required' => false,
                'value' => 0];
            $this->inputsHelper()->select('dependencia', $options);
        } else {
            $this->inputsHelper()->hidden('dependencia', ['value' => 0]);
        }

        $this->inputsHelper()->dynamic('situacaoMatricula', ['value' => 10]);

        // $this->inputsHelper()->text('criterio_aprovacao', [
        //     'label' => 'Critérios de aprovação',
        //     'size' => 39,
        //     'required' => false
        // ]);

        // $this->inputsHelper()->text('texto_rodape', [
        //     'label' => 'Texto do rodapé',
        //     'value' => '',
        //     'placeholder' => '',
        //     'size' => 42,
        //     'required' => false
        // ]);
        $this->inputsHelper()->text('data', [
            'label' => 'Data de encerramento',
            'value' => '',
            'placeholder' => 'DD/MM/AAAA',
            'size' => 40,
            'required' => true
        ]);

        $this->loadResourceAssets($this->getDispatcher());

        // $mensagemAprovacaoPontos = 'Se esta opção for selecionada, a informação (' . $GLOBALS['coreExt']['Config']->report->portaria_aprovacao_pontos . ') será apresentada';

        // $this->inputsHelper()->checkbox('mostrar_msg', ['label' => 'Permitir aprovação de pontos?', $mensagemAprovacaoPontos]);
        // $this->inputsHelper()->checkbox('selecionar_areas_conhecimento', ['label' => 'Selecionar áreas de conhecimento']);
        // $helperOptions = ['objectName' => 'areaconhecimento'];
        // $options = ['label' => 'Áreas de conhecimento', 'size' => 50, 'required' => false, 'placeholder' => 'Todas', 'options' => ['value' => null]];
        // $this->inputsHelper()->multipleSearchAreasConhecimento('', $options, $helperOptions);
        $this->inputsHelper()->text('alterar_nome_diretor', ['label' => 'Alterar nome do(a) diretor(a)', 'value' => false, 'required' => false]);
        $this->inputsHelper()->text('alterar_nome_secretario', ['label' => 'Alterar nome do(a) secretário(a) escolar', 'value' => false, 'required' => false]);
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
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('modelo', (int) 1);
        $this->report->addArg('orientacao', (string) $this->getRequest()->orientacao);
        // $this->report->addArg('texto_rodape', (string) $this->getRequest()->texto_rodape);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('dependencia', (int) $this->getRequest()->dependencia);

        $months = array (1=>'Janeiro',2=>'Fevereiro',3=>'Marco',4=>'Abril',5=>'Maio',6=>'Junho',
                        7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro');
        $data_part = explode('/', $this->getRequest()->data);
        $this->report->addArg('data_dia', (int) $data_part[0]);
        $this->report->addArg('data_mes', $months[(int) $data_part[1]]);
        $this->report->addArg('data_ano', (int) $data_part[2]);
        // $this->report->addArg('criterio_aprovacao', (string) Portabilis_String_Utils::toUtf8($this->getRequest()->criterio_aprovacao));
        // $this->report->addArg('portaria_aprovacao_pontos', Portabilis_String_Utils::toUtf8((string) $GLOBALS['coreExt']['Config']->report->portaria_aprovacao_pontos));
        // $this->report->addArg('mostrar_msg', (bool) $this->getRequest()->mostrar_msg);
        // $areasConhecimento = implode(',',  array_filter($this->getRequest()->areaconhecimento ?? []));
        // $this->report->addArg('areas_conhecimento', trim($areasConhecimento) == '' ? 0 : $areasConhecimento);
        $this->report->addArg('alterar_nome_diretor', $this->getRequest()->alterar_nome_diretor);
        $this->report->addArg('alterar_nome_secretario', $this->getRequest()->alterar_nome_secretario);
    }

    /**
     * @return FinalResultReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new FinalResultReport();
    }
}
