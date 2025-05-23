<?php

use iEducar\Reports\JsonDataSource;

class StudentsPerClassReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        switch ($this->args['modelo']) {
            case 1:
                $model = 'students-per-class';
                break;
            case 2:
                $model = 'students-per-class-simplified';
                break;
            case 3:
                $model = 'students-per-class-basic';                
                break;
            default:
                $model = 'students-per-class';
        }
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('situacao');
        $this->addRequiredArg('modelo');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        return "
            SELECT *
            FROM (
                SELECT
                    DISTINCT ON (aluno.cod_aluno) aluno.cod_aluno AS cod_aluno,
                    matricula_turma.sequencial_fechamento AS sequencial_fechamento,
                    public.formata_cpf(cpf) AS cpf,
                    fcn_upper(pessoa.nome) AS nome_aluno,
                    municipio.nome AS naturalidade,
                    fisica.sus AS codigo_sus,
                    relatorio.get_pai_aluno(aluno.cod_aluno) AS nome_do_pai,
                    relatorio.get_mae_aluno(aluno.cod_aluno) AS nome_da_mae,
                    (
                        CASE WHEN fisica.sexo = 'M' THEN 'M' ELSE 'F' END
                    ) AS sexo,
                    to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                    (
                        CASE
                            WHEN EXTRACT (MONTH from fisica.data_nasc) <= 3
                                AND EXTRACT (DAY from fisica.data_nasc) <= 30
                            THEN '{$this->args['ano']}' - EXTRACT (YEAR from fisica.data_nasc)
                            ELSE '{$this->args['ano']}' - EXTRACT (YEAR from fisica.data_nasc) - 1
                        END
                    ) AS idade,
                    fisica.nis_pis_pasep AS nis,
                    curso.nm_curso AS nome_curso,
                    turma.nm_turma AS nome_turma,
                    turma.multiseriada AS multisseriada,
                    (
                        SELECT s.nm_serie
                        FROM pmieducar.serie s
                        WHERE s.cod_serie = matricula.ref_ref_cod_serie
                    ) AS serie_matricula,
                    turma.cod_turma AS cod_turma,
                    escola.cod_escola AS cod_escola,
                    juridica.fantasia AS nm_escola,
                    turma_turno.nome AS periodo,
                    raca.nm_raca AS cor_raca,     
                    (
                        SELECT pessoa_pai.ocupacao
                        FROM cadastro.fisica AS pessoa_pai
                        WHERE pessoa_pai.idpes = fisica.idpes_pai
                    ) AS profissao_pai,           
                    (
                        SELECT pessoa_mae.ocupacao
                        FROM cadastro.fisica AS pessoa_mae
                        WHERE pessoa_mae.idpes = fisica.idpes_mae
                    ) AS profissao_mae,
                    (CASE
                         WHEN COALESCE(aluno.tipo_transporte, transporte_aluno.responsavel) = 0 THEN 'N'
                         ELSE 'S'
                     END) AS transporte_aluno,
                    (
                        SELECT CASE WHEN (COUNT(d.nm_deficiencia) > 0) THEN 'S' ELSE 'N' END
                        FROM cadastro.deficiencia d
                        INNER JOIN cadastro.fisica_deficiencia fd ON fd.ref_cod_deficiencia = d.cod_deficiencia
                        WHERE fd.ref_idpes = fisica.idpes
                        LIMIT 1
                    ) AS deficiencia,
                    (
                        SELECT CASE WHEN (COUNT(aluno_beneficio.nm_beneficio) > 0) THEN 'S' ELSE 'N' END
                        FROM pmieducar.aluno_beneficio
                        INNER JOIN pmieducar.aluno_aluno_beneficio ON pmieducar.aluno_aluno_beneficio.aluno_id = aluno.cod_aluno
                        WHERE pmieducar.aluno_beneficio.cod_aluno_beneficio = pmieducar.aluno_aluno_beneficio.aluno_beneficio_id
                    ) AS beneficio,
                    (
                        SELECT COALESCE(
                            (
                                SELECT logradouro.nome
                                FROM public.logradouro
                                INNER JOIN cadastro.endereco_pessoa ON logradouro.idlog = endereco_pessoa.idlog
                                WHERE endereco_pessoa.idpes = pessoa.idpes
                            ),
                            (
                                SELECT endereco_externo.logradouro
                                FROM cadastro.endereco_externo
                                WHERE endereco_externo.idpes = aluno.ref_idpes
                            )
                        )
                    ) AS endereco,
                    view_situacao.texto_situacao AS situacao,
                    matricula.dependencia
                FROM
                    pmieducar.instituicao
                INNER JOIN pmieducar.escola ON TRUE
                    AND escola.ref_cod_instituicao = instituicao.cod_instituicao
                INNER JOIN pmieducar.escola_ano_letivo ON TRUE
                    AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
                INNER JOIN pmieducar.escola_curso ON TRUE
                    AND escola_curso.ativo = 1
                    AND escola_curso.ref_cod_escola = escola.cod_escola
                INNER JOIN pmieducar.curso ON TRUE
                    AND curso.cod_curso = escola_curso.ref_cod_curso
                    AND curso.ativo = 1
                INNER JOIN pmieducar.escola_serie ON TRUE
                    AND escola_serie.ativo = 1
                    AND escola_serie.ref_cod_escola = escola.cod_escola
                INNER JOIN pmieducar.serie ON TRUE
                    AND serie.cod_serie = escola_serie.ref_cod_serie
                    AND serie.ativo = 1
                INNER JOIN pmieducar.turma ON TRUE
                    AND turma.ref_ref_cod_escola = escola.cod_escola
                    AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                    AND turma.ativo = 1
                INNER JOIN pmieducar.matricula_turma ON TRUE
                    AND matricula_turma.ref_cod_turma = turma.cod_turma
                    AND (matricula_turma.ativo = 1 OR matricula_turma.transferido IS NOT NULL)
                INNER JOIN pmieducar.matricula ON TRUE
                    AND matricula.cod_matricula = matricula_turma.ref_cod_matricula
                    AND matricula.ativo = 1
                INNER JOIN relatorio.view_situacao ON TRUE
                    AND view_situacao.cod_matricula = matricula.cod_matricula
                    AND view_situacao.cod_turma = turma.cod_turma
                    AND view_situacao.cod_situacao = '{$this->args['situacao']}'
                    AND matricula_turma.sequencial = view_situacao.sequencial
                LEFT JOIN pmieducar.turma_turno ON TRUE
                    AND turma_turno.id = turma.turma_turno_id
                    AND turma.cod_turma = matricula_turma.ref_cod_turma
                INNER JOIN pmieducar.aluno ON TRUE
                    AND matricula.ref_cod_aluno = aluno.cod_aluno
                    AND aluno.ativo = 1
                INNER JOIN cadastro.fisica ON TRUE
                    AND cadastro.fisica.idpes = aluno.ref_idpes
                INNER JOIN cadastro.pessoa ON TRUE
                    AND cadastro.pessoa.idpes = cadastro.fisica.idpes
                LEFT JOIN public.municipio ON municipio.idmun = fisica.idmun_nascimento
                LEFT JOIN cadastro.juridica ON TRUE
                    AND juridica.idpes = escola.ref_idpes
                LEFT JOIN cadastro.documento ON TRUE
                    AND documento.idpes = fisica.idpes
                LEFT JOIN modules.educacenso_cod_aluno ON TRUE
                    AND educacenso_cod_aluno.cod_aluno = aluno.cod_aluno
                LEFT JOIN modules.transporte_aluno ON (aluno.cod_aluno = transporte_aluno.aluno_id)
                LEFT JOIN cadastro.fisica_raca ON (pessoa.idpes = fisica_raca.ref_idpes)
                LEFT JOIN cadastro.raca ON (fisica_raca.ref_cod_raca = raca.cod_raca)
                WHERE TRUE
                    AND pmieducar.instituicao.cod_instituicao = '{$this->args['instituicao']}'
                    AND pmieducar.escola_ano_letivo.ano = '{$this->args['ano']}'
                    AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
                    AND
                    (
                        SELECT CASE WHEN '{$this->args['escola']}' = 0 THEN
                            TRUE
                        ELSE
                            escola.cod_escola = '{$this->args['escola']}'
                        END
                    )
                    AND
                    (
                        SELECT CASE WHEN '{$this->args['curso']}' = 0 THEN
                            TRUE
                        ELSE
                            pmieducar.escola_curso.ref_cod_curso = '{$this->args['curso']}'
                        END
                    )
                    AND
                    (
                        SELECT CASE WHEN '{$this->args['serie']}' = 0 THEN
                            TRUE
                        ELSE
                            pmieducar.serie.cod_serie = '{$this->args['serie']}'
                        END
                    )
                    AND
                    (
                        SELECT CASE WHEN '{$this->args['turma']}' = 0 THEN
                            TRUE
                        ELSE
                            pmieducar.turma.cod_turma = '{$this->args['turma']}'
                        END
                    )
                    AND
                    (
                        CASE WHEN '{$this->args['dependencia']}' = 1 THEN
                            matricula.dependencia = TRUE
                        WHEN '{$this->args['dependencia']}' = 2 THEN
                            matricula.dependencia = FALSE
                        ELSE
                            TRUE
                        END
                    )
                ORDER BY
                    cod_aluno
            ) subquery
            ORDER BY
                    nm_escola ASC,
                    nome_curso ASC,
                    nome_turma ASC,
                    cod_turma ASC,
                    serie_matricula ASC,
                    nome_aluno ASC
        ";
    }
}
