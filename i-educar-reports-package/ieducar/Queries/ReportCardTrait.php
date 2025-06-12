<?php

trait ReportCardTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
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
        $anual = strpos($this->args['dominio'], 'japaratinga') !== false ? 1 : 0;
        
        return <<<SQL
            SELECT fcn_upper(instituicao.nm_instituicao) AS nome_instituicao,
              fcn_upper(instituicao.nm_responsavel) AS nome_responsavel,
              relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
              escola_ano_letivo.ano AS ano,
              view_dados_escola.logradouro AS logradouro,
              view_dados_escola.telefone AS fone,
              view_dados_escola.email AS email,
              curso.nm_curso AS nome_curso,
              serie.nm_serie AS nome_serie,
              turma.nm_turma AS nome_turma,
              public.fcn_upper(pessoa.nome) AS nome_aluno,
              turma_turno.nome AS periodo,
              view_situacao.texto_situacao AS situacao,
              view_componente_curricular.nome AS nome_disciplina,
              area_conhecimento.nome AS area_conhecimento,
              area_conhecimento.secao AS secao,
              CASE WHEN {$anual} = 1 
                THEN COALESCE(nota_etapa4.nota, nota_etapa3.nota, nota_etapa2.nota, nota_etapa1.nota)
                ELSE nota_etapa1.nota 
              END AS nota1num,
              CASE WHEN {$anual} = 1 
                THEN COALESCE(nota_etapa4.nota_arredondada, nota_etapa3.nota_arredondada, nota_etapa2.nota_arredondada, nota_etapa1.nota_arredondada)
                ELSE nota_etapa1.nota_arredondada 
              END AS nota1,
              nota_etapa2.nota AS nota2num,
              nota_etapa2.nota_arredondada AS nota2,
              nota_etapa3.nota AS nota3num,
              nota_etapa3.nota_arredondada AS nota3,
              nota_etapa4.nota AS nota4num,
              nota_etapa4.nota_arredondada AS nota4,
              nota_exame.nota AS nota_exame,
              matricula.cod_matricula AS matricula,
              fisica.data_nasc AS dt_nasc,
              relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 1) AS nota1numturma,
              relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 2) AS nota2numturma,
              relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 3) AS nota3numturma,
              relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 4) AS nota4numturma,
              (
                SELECT falta_etapa.quantidade
                    FROM modules.falta_aluno falta_aluno 
                    INNER JOIN modules.falta_geral falta_etapa ON falta_etapa.falta_aluno_id = falta_aluno.id
                    WHERE falta_aluno.matricula_id = matricula.cod_matricula 
                    AND falta_aluno.tipo_falta = 1
                    AND falta_etapa.etapa = '1' 
              ) AS total_faltas_et1,
              (
                SELECT falta_etapa.quantidade
                    FROM modules.falta_aluno falta_aluno 
                    INNER JOIN modules.falta_geral falta_etapa ON falta_etapa.falta_aluno_id = falta_aluno.id
                    WHERE falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_aluno.tipo_falta = 1
                    AND falta_etapa.etapa = '2' 
              ) AS total_faltas_et2,
              (
                  SELECT falta_etapa.quantidade
                    FROM modules.falta_aluno falta_aluno 
                    INNER JOIN modules.falta_geral falta_etapa ON falta_etapa.falta_aluno_id = falta_aluno.id
                    WHERE falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_aluno.tipo_falta = 1
                    AND falta_etapa.etapa = '3'
              ) AS total_faltas_et3,
              (
                  SELECT falta_etapa.quantidade
                    FROM modules.falta_aluno falta_aluno 
                    INNER JOIN modules.falta_geral falta_etapa ON falta_etapa.falta_aluno_id = falta_aluno.id
                    WHERE falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_aluno.tipo_falta = 1
                    AND falta_etapa.etapa = '4'
              ) AS total_faltas_et4,
              (
                SELECT falta_componente.quantidade
                FROM modules.falta_aluno falta_aluno
                INNER JOIN modules.falta_componente_curricular falta_componente ON falta_componente.falta_aluno_id = falta_aluno.id
                WHERE falta_aluno.matricula_id = matricula.cod_matricula
                  AND falta_aluno.tipo_falta = 2
                  AND falta_componente.componente_curricular_id = view_componente_curricular.id
                  AND falta_componente.etapa = '1'
              ) AS faltas_componente_et1,
              (
                SELECT falta_componente.quantidade
                FROM modules.falta_aluno falta_aluno
                INNER JOIN modules.falta_componente_curricular falta_componente ON falta_componente.falta_aluno_id = falta_aluno.id
                WHERE falta_aluno.matricula_id = matricula.cod_matricula
                  AND falta_aluno.tipo_falta = 2
                  AND falta_componente.componente_curricular_id = view_componente_curricular.id
                  AND falta_componente.etapa = '2'
              ) AS faltas_componente_et2,
              (
                SELECT falta_componente.quantidade
                FROM modules.falta_aluno falta_aluno
                INNER JOIN modules.falta_componente_curricular falta_componente ON falta_componente.falta_aluno_id = falta_aluno.id
                WHERE falta_aluno.matricula_id = matricula.cod_matricula
                  AND falta_aluno.tipo_falta = 2
                  AND falta_componente.componente_curricular_id = view_componente_curricular.id
                  AND falta_componente.etapa = '3'
              ) AS faltas_componente_et3,			   
              (
                SELECT falta_componente.quantidade
                FROM modules.falta_aluno falta_aluno
                INNER JOIN modules.falta_componente_curricular falta_componente ON falta_componente.falta_aluno_id = falta_aluno.id
                WHERE falta_aluno.matricula_id = matricula.cod_matricula
                  AND falta_aluno.tipo_falta = 2
                  AND falta_componente.componente_curricular_id = view_componente_curricular.id
                  AND falta_componente.etapa = '4'
              ) AS faltas_componente_et4,
              relatorio.get_total_geral_falta_componente(matricula.cod_matricula) AS total_geral_faltas_componente,
              relatorio.get_total_faltas(matricula.cod_matricula) AS total_faltas,
              curso.hora_falta AS curso_hora_falta,
              componente_curricular_ano_escolar.carga_horaria::int AS carga_horaria_componente,
              serie.carga_horaria AS carga_horaria_serie,
              serie.dias_letivos,
              nota_componente_curricular_media.media_arredondada AS media,
              nota_componente_curricular_media.media AS medianum,
              nota_exame.nota_arredondada AS nota_exame,
              regra_avaliacao.qtd_casas_decimais,
              regra_avaliacao.tipo_presenca,
              coalesce(regra_avaliacao.media, 0.00) AS media_recuperacao,
              relatorio.get_media_geral_turma(turma.cod_turma, view_componente_curricular.id) AS medianumturma,
              relatorio.get_total_falta_componente(matricula.cod_matricula, view_componente_curricular.id) AS total_faltas_componente
        FROM pmieducar.instituicao
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
        INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma AND view_componente_curricular.cod_serie = serie.cod_serie)
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
        LEFT JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                                                               AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                               AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos)
                                                               )
        LEFT JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
        LEFT JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
        WHERE instituicao.cod_instituicao = {$instituicao}
         AND escola.cod_escola = {$escola}
         AND curso.cod_curso = {$curso}
         AND serie.cod_serie = {$serie}
         AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
         AND escola_ano_letivo.ano = {$ano}
         AND view_situacao.cod_situacao = {$situacao_matricula}
         AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(aluno.cod_aluno, {$alunos_diferenciados})
         AND (CASE WHEN {$matricula} = 0 THEN TRUE ELSE matricula.cod_matricula = {$matricula} END)
         AND (CASE WHEN {$tipo_nota} = 0 THEN TRUE ELSE componente_curricular_ano_escolar.tipo_nota IS NULL OR componente_curricular_ano_escolar.tipo_nota = {$tipo_nota} END)
        ORDER BY sequencial_fechamento,
                relatorio.get_texto_sem_caracter_especial(pessoa.nome),
                area_conhecimento.ordenamento_ac,
                view_componente_curricular.ordenamento,
                view_componente_curricular.nome
SQL;
    }
}
