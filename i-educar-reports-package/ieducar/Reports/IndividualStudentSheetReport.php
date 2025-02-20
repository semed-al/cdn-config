<?php

use iEducar\Reports\JsonDataSource;

class IndividualStudentSheetReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public function templateName()
    {
        return 'individual-student-sheet';
    }

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
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();
        $datasets = $this->getJsonDataFromDatasets();

        return array_merge([
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ], $datasets);
    }

    public function getJsonDataFromDatasets()
    {
        $queriesDatasets = $this->getSqlsForDatasets();
        $jsonData = [];
        foreach ($queriesDatasets as $name => $query) {
            $jsonData[$name] = Portabilis_Utils_Database::fetchPreparedQuery($query);
        }
        return $jsonData;
    }

    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $situacao_matricula = $this->args['situacao_matricula'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "
        SELECT escola_ano_letivo.ano,
            curso.nm_curso as nome_curso,
            serie.nm_serie as nome_serie,
            turma.nm_turma as nome_turma,
            COALESCE(turma_turno.nome, 'não informado') as periodo,
            aluno.cod_aluno as cod_aluno,
            matricula.cod_matricula as cod_matricula,
            to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
            INITCAP(relatorio.get_pai_aluno(aluno.cod_aluno)) AS nome_do_pai,
            INITCAP(relatorio.get_mae_aluno(aluno.cod_aluno)) AS nome_da_mae,
            public.fcn_upper(pessoa.nome) as nome_aluno,
            public.data_para_extenso(CURRENT_DATE) as data_atual,
            fcn_upper(pessoa_gestor.nome) AS nome_diretor,
            fcn_upper(pessoa_secr.nome) AS nome_secretario,
            (
                SELECT STRING_AGG(componente_curricular.nome, ', ')
                FROM relatorio.view_componente_curricular
                INNER JOIN modules.componente_curricular ON (componente_curricular.id = view_componente_curricular.id)
                INNER JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie 
                                                                            AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                                            AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos))
                INNER JOIN modules.area_conhecimento ON (area_conhecimento.id = view_componente_curricular.area_conhecimento_id)
                WHERE view_componente_curricular.cod_turma = turma.cod_turma 
                    AND view_componente_curricular.cod_serie = serie.cod_serie
                    AND componente_curricular.desconsidera_para_progressao = true
                    AND area_conhecimento.nome NOT LIKE '%Ficha%'
            ) AS merge_disciplinas_desconsideradas_aprovacao
        FROM pmieducar.instituicao
        INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
        INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
        INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
        INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
				AND serie.ativo = 1)
        INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
				AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                AND (turma.ref_ref_cod_serie = escola_serie.ref_cod_serie 
                        OR turma.cod_turma IN 
                            (SELECT DISTINCT turma_serie.turma_id 
                                FROM pmieducar.turma_serie 
                                WHERE turma_serie.escola_id = escola.cod_escola
                                    AND turma_serie.serie_id = escola_serie.ref_cod_serie
                                    AND turma_serie.turma_id = turma.cod_turma)
                    )
				AND turma.ativo = 1)
        INNER JOIN pmieducar.curso ON (escola_curso.ref_cod_escola = escola.cod_escola
                AND curso.ativo = 1)
        INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
        INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                    AND matricula.ativo = 1)
        LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
                    AND turma.cod_turma = matricula_turma.ref_cod_turma)
        INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
        INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
        INNER JOIN cadastro.pessoa ON (pessoa.idpes = fisica.idpes)
        INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula AND view_situacao.cod_turma = turma.cod_turma)
        LEFT JOIN cadastro.pessoa pessoa_gestor ON pessoa_gestor.idpes = escola.ref_idpes_gestor
        LEFT JOIN cadastro.pessoa pessoa_secr ON pessoa_secr.idpes = escola.ref_idpes_secretario_escolar
        WHERE instituicao.cod_instituicao = {$instituicao}
            AND escola.cod_escola = {$escola}
            AND escola_ano_letivo.ano = {$ano}
            AND matricula.ano = escola_ano_letivo.ano
            AND (CASE WHEN {$curso} = 0 THEN true ELSE curso.cod_curso = {$curso} END)
            AND (CASE WHEN {$serie} = 0 THEN true ELSE serie.cod_serie = {$serie} END)
            AND (CASE WHEN {$turma} = 0 THEN true ELSE turma.cod_turma = {$turma} END)
            AND (CASE WHEN {$matricula} = 0 THEN true ELSE matricula.cod_matricula = {$matricula} END)
            AND view_situacao.cod_situacao = {$situacao_matricula}
        ORDER BY pessoa.nome
        ";
    }

    public function getSqlsForDatasets()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $situacao_matricula = $this->args['situacao_matricula'] ?: 0;
        $alunos_diferenciados = $this->args['alunos_diferenciados'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;
        $tipo_nota = $this->args['tipo_nota'] ?: 0;

    $studentSheetFrequency = "
        WITH etapa_desc AS (
            SELECT COALESCE(modulo1.nm_tipo, modulo2.nm_tipo) as tipo
            FROM pmieducar.turma turma 
                LEFT JOIN pmieducar.turma_modulo ON turma_modulo.ref_cod_turma = turma.cod_turma
                LEFT JOIN pmieducar.modulo modulo1 ON modulo1.cod_modulo = turma_modulo.ref_cod_modulo
                LEFT JOIN pmieducar.ano_letivo_modulo ON ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
                LEFT JOIN pmieducar.modulo modulo2 ON modulo2.cod_modulo = ano_letivo_modulo.ref_cod_modulo
            WHERE turma.cod_turma = {$turma}
            GROUP BY tipo
            LIMIT 1
        ),
        etapas AS (
            -- 
            SELECT (CASE WHEN etapa_desc.tipo LIKE '%Bimestre%' THEN 'Bim'  
                        WHEN etapa_desc.tipo LIKE '%Semestre%' THEN 'Sem'
                        ELSE etapa_desc.tipo END) AS tipo, 
                    COALESCE(MAX(turma_modulo.sequencial), MAX(ano_letivo_modulo.sequencial)) AS qtd
            FROM etapa_desc
                INNER JOIN pmieducar.turma turma ON turma.ativo = 1
                LEFT JOIN pmieducar.turma_modulo ON turma_modulo.ref_cod_turma = turma.cod_turma
                LEFT JOIN pmieducar.ano_letivo_modulo ON ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
            WHERE turma.cod_turma = {$turma}
            GROUP BY tipo
            LIMIT 1
        )
        SELECT view_componente_curricular.nome AS nome_disciplina,
                matricula.cod_matricula as cod_matricula,
                area_conhecimento.nome AS area_conhecimento,
                falta_etapa1.quantidade AS total_faltas_et1,
                falta_etapa2.quantidade AS total_faltas_et2,
                falta_etapa3.quantidade AS total_faltas_et3,
                falta_etapa4.quantidade AS total_faltas_et4,
                falta_componente1.quantidade AS faltas_componente_et1,
                falta_componente2.quantidade AS faltas_componente_et2,
                falta_componente3.quantidade AS faltas_componente_et3,
                falta_componente4.quantidade AS faltas_componente_et4,
                COALESCE(relatorio.get_total_geral_falta_componente(matricula.cod_matricula),(COALESCE(falta_etapa1.quantidade,0) + COALESCE(falta_etapa2.quantidade,0) + COALESCE(falta_etapa3.quantidade,0) + COALESCE(falta_etapa4.quantidade,0))) AS total_faltas,
                round((modules.frequencia_da_matricula(matricula.cod_matricula))::numeric, 1) AS percentual_frequencia_final,
                COALESCE(componente_curricular_turma.carga_horaria::int, escola_serie_disciplina.carga_horaria::int, componente_curricular_ano_escolar.carga_horaria::int, view_componente_curricular.carga_horaria) AS carga_horaria_componente,
                COALESCE(componente_curricular_turma.carga_horaria::int, escola_serie_disciplina.carga_horaria::int, componente_curricular_ano_escolar.carga_horaria::int, view_componente_curricular.carga_horaria)/etapas.qtd AS carga_horaria_componente_et,
                CEIL((COALESCE(componente_curricular_turma.carga_horaria::int, escola_serie_disciplina.carga_horaria::int, componente_curricular_ano_escolar.carga_horaria::int, view_componente_curricular.carga_horaria)/etapas.qtd)/curso.hora_falta) AS aulas_dadas_componente,
                CEIL(COALESCE(componente_curricular_turma.carga_horaria::int, escola_serie_disciplina.carga_horaria::int, componente_curricular_ano_escolar.carga_horaria::int, view_componente_curricular.carga_horaria)/etapas.qtd) AS aulas_dadas_componente_et,
                serie.carga_horaria AS carga_horaria_serie,
                curso.hora_falta AS hora_falta,
                serie.dias_letivos,
                falta_aluno.id AS falta_aluno_id,
                relatorio.get_total_falta_componente(matricula.cod_matricula, view_componente_curricular.id) AS total_faltas_componente,
                (relatorio.get_total_falta_componente(matricula.cod_matricula, view_componente_curricular.id)*curso.hora_falta/COALESCE(componente_curricular_turma.carga_horaria::int, escola_serie_disciplina.carga_horaria::int, componente_curricular_ano_escolar.carga_horaria::int, view_componente_curricular.carga_horaria))*100 AS percentual_falta_componente,
                regra_avaliacao.tipo_presenca,
                matricula_turma.ativo AS ativo_na_turma,
                COALESCE(matricula_turma.transferido, false) AS saiu_da_turma,
                CAST(matricula_turma.data_exclusao as DATE) AS data_saida,
                COALESCE(turma_modulo.sequencial, ano_letivo_modulo.sequencial, 0) AS saiu_etapa,
                etapas.qtd AS quantidade_etapas,
                etapas.tipo AS etapa_tipo,
                (CAST(matricula_turma.data_exclusao as DATE) - COALESCE(turma_modulo.data_inicio, ano_letivo_modulo.data_inicio))::float/(COALESCE(turma_modulo.data_fim, ano_letivo_modulo.data_fim) - COALESCE(turma_modulo.data_inicio, ano_letivo_modulo.data_inicio))::float AS percentual_cursado_na_etapa_decimal
        FROM etapas
        INNER JOIN pmieducar.instituicao ON instituicao.ativo = 1
        INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
        INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
        INNER JOIN relatorio.view_dados_escola ON (view_dados_escola.cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                            AND escola_curso.ref_cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                        AND curso.ativo = 1)
        INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                            AND escola_serie.ref_cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                        AND serie.ativo = 1)
        INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                        AND turma.ativo = 1)
        INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma 
                AND view_componente_curricular.cod_serie = serie.cod_serie)
        INNER JOIN modules.area_conhecimento ON (area_conhecimento.id = view_componente_curricular.area_conhecimento_id)
        INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
        INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                            AND matricula.ref_ref_cod_escola = escola.cod_escola
                                            AND matricula.ref_cod_curso = curso.cod_curso
                                            AND matricula.ref_ref_cod_serie = serie.cod_serie
                                            AND matricula.ano = turma.ano
                                            AND matricula.ativo = 1)
        INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                                AND view_situacao.cod_turma = turma.cod_turma
                                                AND matricula_turma.sequencial = view_situacao.sequencial)
        LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
                                            AND turma.cod_turma = matricula_turma.ref_cod_turma)
        LEFT JOIN pmieducar.turma_modulo ON (turma_modulo.ref_cod_turma = matricula_turma.ref_cod_turma
                                            AND turma_modulo.data_inicio <= matricula_turma.data_exclusao 
                                            AND matricula_turma.data_exclusao <= turma_modulo.data_fim)
        LEFT JOIN pmieducar.ano_letivo_modulo ON (ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
                                            AND ano_letivo_modulo.ref_ano = {$ano} 
                                            AND ano_letivo_modulo.data_inicio <= matricula_turma.data_exclusao
                                            AND matricula_turma.data_exclusao <= ano_letivo_modulo.data_fim)
        INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
        INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
        INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
        LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
        LEFT JOIN modules.falta_geral falta_etapa1 ON (falta_etapa1.falta_aluno_id = falta_aluno.id
                                                        AND falta_etapa1.etapa = '1')
        LEFT JOIN modules.falta_geral falta_etapa2 ON (falta_etapa2.falta_aluno_id = falta_aluno.id
                                                        AND falta_etapa2.etapa = '2')
        LEFT JOIN modules.falta_geral falta_etapa3 ON (falta_etapa3.falta_aluno_id = falta_aluno.id
                                                        AND falta_etapa3.etapa = '3')
        LEFT JOIN modules.falta_geral falta_etapa4 ON (falta_etapa4.falta_aluno_id = falta_aluno.id
                                                        AND falta_etapa4.etapa = '4')
        LEFT JOIN modules.falta_componente_curricular falta_componente1 ON (falta_componente1.falta_aluno_id = falta_aluno.id
                                                                            AND falta_componente1.componente_curricular_id = view_componente_curricular.id
                                                                            AND falta_componente1.etapa = '1')
        LEFT JOIN modules.falta_componente_curricular falta_componente2 ON (falta_componente2.falta_aluno_id = falta_aluno.id
                                                                            AND falta_componente2.componente_curricular_id = view_componente_curricular.id
                                                                            AND falta_componente2.etapa = '2')
        LEFT JOIN modules.falta_componente_curricular falta_componente3 ON (falta_componente3.falta_aluno_id = falta_aluno.id
                                                                            AND falta_componente3.componente_curricular_id = view_componente_curricular.id
                                                                            AND falta_componente3.etapa = '3')
        LEFT JOIN modules.falta_componente_curricular falta_componente4 ON (falta_componente4.falta_aluno_id = falta_aluno.id
                                                                            AND falta_componente4.componente_curricular_id = view_componente_curricular.id
                                                                            AND falta_componente4.etapa = '4')
        INNER JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                                                                AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                                AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos)
                                                                )
        LEFT JOIN modules.componente_curricular_turma ON (componente_curricular_turma.ano_escolar_id = serie.cod_serie
                                                                AND componente_curricular_turma.componente_curricular_id = view_componente_curricular.id
                                                                AND componente_curricular_turma.turma_id = turma.cod_turma
                                                                )
        LEFT JOIN pmieducar.escola_serie_disciplina ON (escola_serie_disciplina.ref_ref_cod_serie = serie.cod_serie
                                                                AND escola_serie_disciplina.ref_cod_disciplina = view_componente_curricular.id
                                                                AND escola_serie_disciplina.ref_ref_cod_escola = escola.cod_escola
                                                                AND escola_serie_disciplina.ativo = 1
                                                                AND matricula.ano = any(escola_serie_disciplina.anos_letivos)
                                                                )                                                        
        LEFT JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
        LEFT JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
        WHERE instituicao.cod_instituicao = {$instituicao}
        AND escola.cod_escola = {$escola}
        AND curso.cod_curso = {$curso}
        AND serie.cod_serie = {$serie}
        AND turma.cod_turma = {$turma}
        AND escola_ano_letivo.ano = {$ano}
        AND view_situacao.cod_situacao = {$situacao_matricula}
        AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(aluno.cod_aluno, {$alunos_diferenciados})
        AND (CASE WHEN {$matricula} = 0 THEN TRUE ELSE matricula.cod_matricula = {$matricula} END)
        AND area_conhecimento.nome NOT LIKE '%Ficha%'
        ORDER BY sequencial_fechamento,
                relatorio.get_texto_sem_caracter_especial(pessoa.nome),
                view_componente_curricular.ordenamento,
                view_componente_curricular.nome;
        ";

    $studentSheetPerformance = "
        WITH etapa_desc AS (
            SELECT COALESCE(modulo1.nm_tipo, modulo2.nm_tipo) as tipo
            FROM pmieducar.turma turma 
                LEFT JOIN pmieducar.turma_modulo ON turma_modulo.ref_cod_turma = turma.cod_turma
                LEFT JOIN pmieducar.modulo modulo1 ON modulo1.cod_modulo = turma_modulo.ref_cod_modulo
                LEFT JOIN pmieducar.ano_letivo_modulo ON ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
                LEFT JOIN pmieducar.modulo modulo2 ON modulo2.cod_modulo = ano_letivo_modulo.ref_cod_modulo
            WHERE turma.cod_turma = {$turma}
            GROUP BY tipo
            LIMIT 1
        ),
        etapas AS (
            -- 
            SELECT (CASE WHEN etapa_desc.tipo LIKE '%Bimestre%' THEN 'Bim'  
                        WHEN etapa_desc.tipo LIKE '%Semestre%' THEN 'Sem'
                        ELSE etapa_desc.tipo END) AS tipo, 
                    COALESCE(MAX(turma_modulo.sequencial), MAX(ano_letivo_modulo.sequencial)) AS qtd
            FROM etapa_desc
                INNER JOIN pmieducar.turma turma ON turma.ativo = 1
                LEFT JOIN pmieducar.turma_modulo ON turma_modulo.ref_cod_turma = turma.cod_turma
                LEFT JOIN pmieducar.ano_letivo_modulo ON ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
            WHERE turma.cod_turma = {$turma}
            GROUP BY tipo
            LIMIT 1
        )
        SELECT view_situacao.texto_situacao AS situacao,
                view_componente_curricular.nome AS nome_disciplina,
                componente_curricular.desconsidera_para_progressao,
                matricula.cod_matricula as cod_matricula,
                area_conhecimento.nome AS area_conhecimento,
                CASE WHEN regra_avaliacao.media = 0 THEN NULL ELSE nota_etapa1.nota_arredondada END AS nota1,
                nota_etapa1.nota_recuperacao AS nota1recuperacao,
                CASE WHEN regra_avaliacao.media = 0 THEN NULL ELSE nota_etapa2.nota_arredondada END AS nota2,
                nota_etapa2.nota_recuperacao AS nota2recuperacao,
                CASE WHEN regra_avaliacao.media = 0 THEN NULL ELSE nota_etapa3.nota_arredondada END AS nota3,
                nota_etapa3.nota_recuperacao AS nota3recuperacao,
                CASE WHEN regra_avaliacao.media = 0 THEN NULL ELSE nota_etapa4.nota_arredondada END AS nota4,
                nota_etapa4.nota_recuperacao AS nota4recuperacao,
                (COALESCE(nota_etapa1.nota,0) + COALESCE(nota_etapa2.nota,0) + COALESCE(nota_etapa3.nota,0) + COALESCE(nota_etapa4.nota,0)) AS resultado_anual,
                ROUND((COALESCE(nota_etapa1.nota,0) + COALESCE(nota_etapa2.nota,0) + COALESCE(nota_etapa3.nota,0) + COALESCE(nota_etapa4.nota,0))/etapas.qtd, 2) AS media_anual,
                nota_exame.nota AS nota_exame,
                CASE WHEN regra_avaliacao.media = 0 THEN NULL ELSE nota_componente_curricular_media.media_arredondada END AS media_final,
                CASE WHEN regra_avaliacao.media = 0 THEN NULL ELSE nota_componente_curricular_media.media END AS medianum,
                nota_exame.nota_arredondada AS nota_exame,
                regra_avaliacao.qtd_casas_decimais,
                CASE WHEN regra_avaliacao.media = 0 THEN false ELSE true END AS tem_nota,
                matricula_turma.ativo AS ativo_na_turma,
                COALESCE(matricula_turma.transferido, false) AS saiu_da_turma,
                CAST(matricula_turma.data_exclusao as DATE) AS data_saida,
                COALESCE(turma_modulo.sequencial, ano_letivo_modulo.sequencial, 0) AS saiu_etapa,
                (
                    SELECT MAX(COALESCE(falta_etapa.etapa, falta_componente.etapa))
                    FROM modules.falta_aluno
                    LEFT JOIN modules.falta_geral falta_etapa ON (falta_etapa.falta_aluno_id = falta_aluno.id)
                    LEFT JOIN modules.falta_componente_curricular falta_componente ON (falta_componente.falta_aluno_id = falta_aluno.id
                        AND falta_componente.componente_curricular_id = view_componente_curricular.id)
                    WHERE falta_aluno.matricula_id = matricula.cod_matricula
                ) AS cursou_etapa_max,
                etapas.qtd AS quantidade_etapas,
                etapas.tipo AS etapa_tipo
        FROM etapas
        INNER JOIN pmieducar.instituicao ON instituicao.ativo = 1
        INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
        INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                            AND escola_curso.ref_cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                        AND curso.ativo = 1)
        INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                            AND escola_serie.ref_cod_escola = escola.cod_escola)
        INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                        AND serie.ativo = 1)
        INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                        AND turma.ativo = 1)
        INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma 
                    AND view_componente_curricular.cod_serie = serie.cod_serie)
        INNER JOIN modules.area_conhecimento ON (area_conhecimento.id = view_componente_curricular.area_conhecimento_id)
        INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
        INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                            AND matricula.ref_ref_cod_escola = escola.cod_escola
                                            AND matricula.ref_cod_curso = curso.cod_curso
                                            AND matricula.ref_ref_cod_serie = serie.cod_serie
                                            AND matricula.ano = turma.ano
                                            AND matricula.ativo = 1)
        INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                                AND view_situacao.cod_turma = turma.cod_turma
                                                AND matricula_turma.sequencial = view_situacao.sequencial)
        LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
                                            AND turma.cod_turma = matricula_turma.ref_cod_turma)
        LEFT JOIN pmieducar.turma_modulo ON (turma_modulo.ref_cod_turma = matricula_turma.ref_cod_turma
                                            AND turma_modulo.data_inicio <= matricula_turma.data_exclusao 
                                            AND matricula_turma.data_exclusao <= turma_modulo.data_fim)
        LEFT JOIN pmieducar.ano_letivo_modulo ON (ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
                                            AND ano_letivo_modulo.ref_ano = {$ano} 
                                            AND ano_letivo_modulo.data_inicio <= matricula_turma.data_exclusao
                                            AND matricula_turma.data_exclusao <= ano_letivo_modulo.data_fim)
        INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
        INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
        INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
        LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
        LEFT JOIN modules.nota_componente_curricular nota_etapa1 ON (nota_etapa1.nota_aluno_id = nota_aluno.id
                                                                    AND nota_etapa1.componente_curricular_id = view_componente_curricular.id
                                                                    AND nota_etapa1.etapa = '1')
        LEFT JOIN modules.nota_componente_curricular nota_etapa2 ON (nota_etapa2.nota_aluno_id = nota_aluno.id
                                                                    AND nota_etapa2.componente_curricular_id = view_componente_curricular.id
                                                                    AND nota_etapa2.etapa = '2')
        LEFT JOIN modules.nota_componente_curricular nota_etapa3 ON (nota_etapa3.nota_aluno_id = nota_aluno.id
                                                                    AND nota_etapa3.componente_curricular_id = view_componente_curricular.id
                                                                    AND nota_etapa3.etapa = '3')
        LEFT JOIN modules.nota_componente_curricular nota_etapa4 ON (nota_etapa4.nota_aluno_id = nota_aluno.id
                                                                    AND nota_etapa4.componente_curricular_id = view_componente_curricular.id
                                                                    AND nota_etapa4.etapa = '4')
        LEFT JOIN modules.nota_componente_curricular nota_exame ON (nota_exame.nota_aluno_id = nota_aluno.id
                                                                    AND nota_exame.componente_curricular_id = view_componente_curricular.id
                                                                    AND nota_exame.etapa = 'Rc')
        LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.nota_aluno_id = nota_aluno.id
                                                                AND nota_componente_curricular_media.componente_curricular_id = view_componente_curricular.id)
        INNER JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                                                                AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                                AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos)
                                                                )
        INNER JOIN modules.componente_curricular ON (componente_curricular.id = componente_curricular_ano_escolar.componente_curricular_id)
        LEFT JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
        LEFT JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
        WHERE instituicao.cod_instituicao = {$instituicao}
        AND escola.cod_escola = {$escola}
        AND curso.cod_curso = {$curso}
        AND serie.cod_serie = {$serie}
        AND turma.cod_turma = {$turma}
        AND escola_ano_letivo.ano = {$ano}
        AND view_situacao.cod_situacao = {$situacao_matricula}
        AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(aluno.cod_aluno, {$alunos_diferenciados})
        AND (CASE WHEN {$matricula} = 0 THEN TRUE ELSE matricula.cod_matricula = {$matricula} END)
        AND area_conhecimento.nome NOT LIKE '%Ficha%'
        ORDER BY sequencial_fechamento,
                relatorio.get_texto_sem_caracter_especial(pessoa.nome),
                view_componente_curricular.ordenamento,
                view_componente_curricular.nome;
        ";

        return [
            'student_sheet_frequency' => $studentSheetFrequency,
            'student_sheet_performance' => $studentSheetPerformance
        ];
    }
}
