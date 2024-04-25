<?php

class QuerySchoolHistorySeriesYears extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            WITH max_ano (chave,ano) AS (
                -- pega séries cursando ou transferido 
                SELECT 1,he.ano
                FROM pmieducar.historico_escolar he
                WHERE he.ref_cod_aluno = $P{aluno}
                    AND he.aprovado NOT IN (2,6,14,15)
                    AND he.extra_curricular = 0
                    AND he.ativo = 1
                    AND (he.ref_cod_escola IS NULL OR he.ref_cod_escola = $P{escola})                    
                ORDER BY he.ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                LIMIT 1
            ),
            max_ano_aprovado (chave,ano) AS (
                SELECT 1,he.ano
                FROM pmieducar.historico_escolar he
                WHERE he.ref_cod_aluno = $P{aluno}
                    AND he.aprovado NOT IN (2,3,4,6,14,15)
                    AND he.extra_curricular = 0
                    AND he.ativo = 1
                ORDER BY he.ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                LIMIT 1
            )
            SELECT
                COALESCE(max_ano_aprovado.ano, max_ano.ano) AS ano,
                vhsa.cod_aluno,
                trim(vhsa.disciplina) AS nm_disciplina,
                pessoa.nome AS nome_aluno,
                eca.cod_aluno_inep AS cod_inep,
                municipio.nome || ', Estado de ' || municipio.sigla_uf AS cidade_nascimento_uf,
                municipio.sigla_uf AS uf_nascimento,
                municipio.nome AS cidade_nascimento,
                to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
                relatorio.get_pai_aluno(vhsa.cod_aluno) AS nome_do_pai,
                relatorio.get_mae_aluno(vhsa.cod_aluno) AS nome_da_mae,
                hd1.carga_horaria_disciplina AS chd1,
                hd2.carga_horaria_disciplina AS chd2,
                hd3.carga_horaria_disciplina AS chd3,
                hd4.carga_horaria_disciplina AS chd4,
                hd5.carga_horaria_disciplina AS chd5,
                hd6.carga_horaria_disciplina AS chd6,
                hd7.carga_horaria_disciplina AS chd7,
                hd8.carga_horaria_disciplina AS chd8,
                hd9.carga_horaria_disciplina AS chd9,
                relatorio.get_situacao_historico_abreviado(phe1.aprovado) AS status_serie1,
                relatorio.get_situacao_historico_abreviado(phe2.aprovado) AS status_serie2,
                relatorio.get_situacao_historico_abreviado(phe3.aprovado) AS status_serie3,
                relatorio.get_situacao_historico_abreviado(phe4.aprovado) AS status_serie4,
                relatorio.get_situacao_historico_abreviado(phe5.aprovado) AS status_serie5,
                relatorio.get_situacao_historico_abreviado(phe6.aprovado) AS status_serie6,
                relatorio.get_situacao_historico_abreviado(phe7.aprovado) AS status_serie7,
                relatorio.get_situacao_historico_abreviado(phe8.aprovado) AS status_serie8,
                relatorio.get_situacao_historico_abreviado(phe9.aprovado) AS status_serie9,
                phe1.carga_horaria AS carga_horaria1,
                phe2.carga_horaria AS carga_horaria2,
                phe3.carga_horaria AS carga_horaria3,
                phe4.carga_horaria AS carga_horaria4,
                phe5.carga_horaria AS carga_horaria5,
                phe6.carga_horaria AS carga_horaria6,
                phe7.carga_horaria AS carga_horaria7,
                phe8.carga_horaria AS carga_horaria8,
                phe9.carga_horaria AS carga_horaria9,
                phe1.frequencia AS freq1,
                phe2.frequencia AS freq2,
                phe3.frequencia AS freq3,
                phe4.frequencia AS freq4,
                phe5.frequencia AS freq5,
                phe6.frequencia AS freq6,
                phe7.frequencia AS freq7,
                phe8.frequencia AS freq8,
                phe9.frequencia AS freq9,
                hd1.nota AS nota_1serie,
                hd2.nota AS nota_2serie,
                hd3.nota AS nota_3serie,
                hd4.nota AS nota_4serie,
                hd5.nota AS nota_5serie,
                hd6.nota AS nota_6serie,
                hd7.nota AS nota_7serie,
                hd8.nota AS nota_8serie,
                hd9.nota AS nota_9serie,
                phe1.ano AS ano_1serie,
                phe2.ano AS ano_2serie,
                phe3.ano AS ano_3serie,
                phe4.ano AS ano_4serie,
                phe5.ano AS ano_5serie,
                phe6.ano AS ano_6serie,
                phe7.ano AS ano_7serie,
                phe8.ano AS ano_8serie,
                phe9.ano AS ano_9serie,
                phe1.escola AS escola_1serie,
                phe2.escola AS escola_2serie,
                phe3.escola AS escola_3serie,
                phe4.escola AS escola_4serie,
                phe5.escola AS escola_5serie,
                phe6.escola AS escola_6serie,
                phe7.escola AS escola_7serie,
                phe8.escola AS escola_8serie,
                phe9.escola AS escola_9serie,
                phe1.escola_uf AS escola_uf_1serie,
                phe2.escola_uf AS escola_uf_2serie,
                phe3.escola_uf AS escola_uf_3serie,
                phe4.escola_uf AS escola_uf_4serie,
                phe5.escola_uf AS escola_uf_5serie,
                phe6.escola_uf AS escola_uf_6serie,
                phe7.escola_uf AS escola_uf_7serie,
                phe8.escola_uf AS escola_uf_8serie,
                phe9.escola_uf AS escola_uf_9serie,
                phe1.escola_cidade AS escola_cidade_1serie,
                phe2.escola_cidade AS escola_cidade_2serie,
                phe3.escola_cidade AS escola_cidade_3serie,
                phe4.escola_cidade AS escola_cidade_4serie,
                phe5.escola_cidade AS escola_cidade_5serie,
                phe6.escola_cidade AS escola_cidade_6serie,
                phe7.escola_cidade AS escola_cidade_7serie,
                phe8.escola_cidade AS escola_cidade_8serie,
                phe9.escola_cidade AS escola_cidade_9serie,
                COALESCE(phe9.nm_serie, phe8.nm_serie, phe7.nm_serie, phe6.nm_serie, phe5.nm_serie, phe4.nm_serie, phe3.nm_serie, phe2.nm_serie, phe1.nm_serie) as ultima_serie_estudada,
                (
                    CASE
                        WHEN phe1.historico_grade_curso_id = 1 AND LOWER(phe1.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe1.nm_serie,' Série')
                        WHEN phe1.historico_grade_curso_id = 2 AND LOWER(phe1.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe1.nm_serie,' Ano')
                        ELSE phe1.nm_serie
                    END
                ) AS nome_serie1,
                (
                    SELECT CASE
                        WHEN phe2.historico_grade_curso_id = 1 AND LOWER(phe2.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe2.nm_serie,' Série')
                        WHEN phe2.historico_grade_curso_id = 2 AND LOWER(phe2.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe2.nm_serie,' Ano')
                        ELSE phe2.nm_serie
                    END
                ) AS nome_serie2,
                (
                    SELECT CASE 
                        WHEN phe3.historico_grade_curso_id = 1 AND LOWER(phe3.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe3.nm_serie,' Série')
                        WHEN phe3.historico_grade_curso_id = 2 AND LOWER(phe3.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe3.nm_serie,' Ano')
                        ELSE phe3.nm_serie 
                    END
                ) AS nome_serie3,
                (
                    SELECT CASE
                        WHEN phe4.historico_grade_curso_id = 1 AND LOWER(phe4.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe4.nm_serie,' Série')
                        WHEN phe4.historico_grade_curso_id = 2 AND LOWER(phe4.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe4.nm_serie,' Ano')
                        ELSE phe4.nm_serie 
                    END
                ) AS nome_serie4,
                (
                    SELECT CASE
                        WHEN phe5.historico_grade_curso_id = 1 AND LOWER(phe5.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe5.nm_serie,' Série')
                        WHEN phe5.historico_grade_curso_id = 2 AND LOWER(phe5.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe5.nm_serie,' Ano')
                        ELSE phe5.nm_serie                     
                    END                    
                ) AS nome_serie5,
                (
                    SELECT CASE
                        WHEN phe6.historico_grade_curso_id = 1 AND LOWER(phe6.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe6.nm_serie,' Série')
                        WHEN phe6.historico_grade_curso_id = 2 AND LOWER(phe6.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe6.nm_serie,' Ano')
                        ELSE phe6.nm_serie 
                    END                    
                ) AS nome_serie6,
                (
                    SELECT CASE
                        WHEN phe7.historico_grade_curso_id = 1 AND LOWER(phe7.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe7.nm_serie,' Série')
                        WHEN phe7.historico_grade_curso_id = 2 AND LOWER(phe7.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe7.nm_serie,' Ano')
                        ELSE phe7.nm_serie 
                    END                    
                ) AS nome_serie7,
                (
                    SELECT CASE
                        WHEN phe8.historico_grade_curso_id = 1 AND LOWER(phe8.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe8.nm_serie,' Série')
                        WHEN phe8.historico_grade_curso_id = 2 AND LOWER(phe8.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe8.nm_serie,' Ano')
                        ELSE phe8.nm_serie 
                    END                    
                ) AS nome_serie8,
                (
                    SELECT CASE
                        WHEN phe9.historico_grade_curso_id = 1 AND LOWER(phe9.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe9.nm_serie,' Série')
                        WHEN phe9.historico_grade_curso_id = 2 AND LOWER(phe9.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe9.nm_serie,' Ano')
                        ELSE phe9.nm_serie 
                    END
                ) AS nome_serie9,
                (
                    SELECT municipio
                    FROM relatorio.view_dados_escola
                    WHERE cod_escola = $P{escola}
                ) AS municipio,
                (
                    SELECT fcn_upper(p.nome)
                    FROM cadastro.pessoa p
                    INNER JOIN pmieducar.escola e ON (e.ref_idpes_gestor = p.idpes)
                    WHERE e.cod_escola = $P{escola}
                ) AS diretor,
                (
                    SELECT fcn_upper(p.nome)
                    FROM cadastro.pessoa p
                    INNER JOIN pmieducar.escola e ON (p.idpes = e.ref_idpes_secretario_escolar)
                    WHERE e.cod_escola = $P{escola}
                ) AS secretario,
                (
                    SELECT max(COALESCE(ato_poder_publico,''))
                    FROM pmieducar.curso
                ) AS ato_poder_publico,
                (
                    SELECT COALESCE(fcn_upper(nm_curso),'')
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.ativo = 1
                    ORDER BY ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                    LIMIT 1
                ) AS nome_curso,
                to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') AS hora_atual,
                public.data_para_extenso(CURRENT_DATE) AS data_atual_extenso,
                (
                    SELECT
                        CASE
                            WHEN he.aprovado in (3,4) THEN 'está cursando '
                            ELSE 'concluiu '
                        END || (
                                CASE
                                    WHEN (
                                        SELECT substring(he.nm_serie,1,1)::integer
                                        FROM pmieducar.historico_escolar he
                                        WHERE he.ref_cod_aluno = vhsa.cod_aluno
                                        AND he.ativo = 1
                                        AND he.historico_grade_curso_id = 2
                                        ORDER BY he.ano DESC LIMIT 1
                                    ) = 1 
                                    THEN 'o ' || (substring(he.nm_serie,1,1)::integer::numeric) || 'º ano'
                                    ELSE
                                        CASE 
                                            WHEN historico_grade_curso_id = 1 
                                            THEN
                                                CASE
                                                    WHEN substring(nm_serie,1,1)::integer = '0' 
                                                    THEN 'o ' || (substring(nm_serie,1,1)::integer::numeric +1) || 'º ano.' || ' do Ensino Fundamental'
                                                    ELSE 'o ' || (substring(nm_serie,1,1)::integer::numeric +1) || 'º ano/' || substring(nm_serie,1,1)::integer || 'ª série' || ' do Ensino Fundamental'
                                                END
                                            WHEN historico_grade_curso_id = 2
                                            THEN
                                                CASE
                                                    WHEN (substring(nm_serie,1,1)::integer::numeric -1) = '0' 
                                                    THEN 'o ' || substring(nm_serie,1,1)::integer || 'º ano,' || ' do Ensino Fundamental'
                                                    ELSE 'o ' || substring(nm_serie,1,1)::integer || 'º ano/' || (substring(nm_serie,1,1)::integer::numeric -1) || 'ª série' || ' do Ensino Fundamental'
                                                END
                                            ELSE 
                                               'o(a) ' || nm_serie || ' da Educação de Jovens e Adultos (EJA)'
                                        END
                                END
                            )
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.aprovado NOT IN (2,3,6,14,15) -- aprovado (1,12,13) ou reclassificado (5)
                    AND he.extra_curricular = 0
                    AND he.ativo = 1
                    AND he.ano <= max_ano_aprovado.ano
                    ORDER BY he.ano DESC, relatorio.prioridade_historico(he.aprovado) ASC, he.posicao DESC
                    LIMIT 1
                ) AS nome_serie_aux,
                (
                    SELECT COUNT(1)
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.ativo = 1
                    AND (CASE WHEN $P!{nao_emitir_reprovado} THEN he.aprovado <> 2 ELSE 1=1 END)
                    AND he.dependencia = 't'
                ) AS possui_historico_dependencia,
                (
                    SELECT textcat_all(obs)
                    FROM (
                        SELECT concat(observacao,'<br>') AS obs
                        FROM pmieducar.historico_escolar phe
                        WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                        AND phe.ativo = 1
                        AND phe.ano <= max_ano.ano
                        AND LENGTH(observacao) > 0
                        AND (CASE WHEN $P!{nao_emitir_reprovado} THEN phe.aprovado <> 2 ELSE 1=1 END)                        
                        ORDER BY phe.ano
                    )tabl
                ) AS observacao_all,
                (
                    SELECT area.nome
                    FROM modules.area_conhecimento area
                    LEFT JOIN relatorio.view_componente_curricular vcc ON (area.id = vcc.area_conhecimento_id)
                    WHERE relatorio.get_texto_sem_caracter_especial(vcc.nome) = vhsa.disciplina
                    LIMIT 1
                ) AS area_nome,
                (
                    SELECT cc.ordenamento 
                    FROM modules.componente_curricular cc 
                    WHERE relatorio.get_texto_sem_caracter_especial(cc.nome) = vhsa.disciplina
                    ORDER BY cc.ordenamento ASC
                    LIMIT 1
                ) AS ordem_disciplina
                FROM max_ano
                LEFT JOIN max_ano_aprovado ON max_ano.chave = max_ano_aprovado.chave
                INNER JOIN relatorio.view_historico_series_anos vhsa ON vhsa.cod_aluno = $P{aluno} AND (vhsa.ano_1serie <= max_ano.ano OR vhsa.ano_2serie <= max_ano.ano OR vhsa.ano_3serie <= max_ano.ano OR vhsa.ano_4serie <= max_ano.ano OR vhsa.ano_5serie <= max_ano.ano OR vhsa.ano_6serie <= max_ano.ano OR vhsa.ano_7serie <= max_ano.ano OR vhsa.ano_8serie <= max_ano.ano OR vhsa.ano_9serie <= max_ano.ano)
                    AND vhsa.disciplina !~ '([a-zA-Z]{2}[0-9]{2}){2}' 
                INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = vhsa.cod_aluno)
                INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
                LEFT JOIN pmieducar.historico_escolar phe1 ON phe1.ref_cod_aluno = vhsa.cod_aluno AND phe1.ativo = 1 
                    AND (
                            (phe1.posicao is not null AND phe1.posicao = 1) 
                            OR (
                                (phe1.posicao is null)
                                AND
                                (phe1.ano = vhsa.ano_1serie AND phe1.nm_serie LIKE '%1%')
                            )
                        )
                    AND phe1.ano <= max_ano.ano
                    AND (CASE WHEN phe1.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe1.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd1 ON hd1.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd1.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd1.ref_sequencial = phe1.sequencial
                LEFT JOIN pmieducar.historico_escolar phe2 ON phe2.ref_cod_aluno = vhsa.cod_aluno AND phe2.ativo = 1 
                    AND (
                            (phe2.posicao is not null AND phe2.posicao = 2)
                            OR 
                            (
                                (phe2.posicao is null)
                                AND
                                (phe2.ano = vhsa.ano_2serie AND phe2.nm_serie LIKE '%2%')
                            )
                        )
                    AND phe2.ano <= max_ano.ano
                    AND (CASE WHEN phe2.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe2.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd2 ON hd2.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd2.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd2.ref_sequencial = phe2.sequencial
                LEFT JOIN pmieducar.historico_escolar phe3 ON phe3.ref_cod_aluno = vhsa.cod_aluno AND phe3.ativo = 1 
                    AND (
                            (phe3.posicao is not null AND phe3.posicao = 3) 
                            OR 
                            (
                                (phe3.posicao is null)
                                AND
                                (phe3.ano = vhsa.ano_3serie AND phe3.nm_serie LIKE '%3%')
                            )
                        )
                    AND phe3.ano <= max_ano.ano
                    AND (CASE WHEN phe3.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe3.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd3 ON hd3.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd3.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd3.ref_sequencial = phe3.sequencial
                LEFT JOIN pmieducar.historico_escolar phe4 ON phe4.ref_cod_aluno = vhsa.cod_aluno AND phe4.ativo = 1 
                    AND (
                            (phe4.posicao is not null AND phe4.posicao = 4) 
                            OR (
                                (phe4.posicao is null)
                                AND
                                (phe4.ano = vhsa.ano_4serie AND phe4.nm_serie LIKE '%4%')
                            )
                        )
                    AND phe4.ano <= max_ano.ano
                    AND (CASE WHEN phe4.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe4.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd4 ON hd4.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd4.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd4.ref_sequencial = phe4.sequencial
                LEFT JOIN pmieducar.historico_escolar phe5 ON phe5.ref_cod_aluno = vhsa.cod_aluno AND phe5.ativo = 1 
                    AND ( 
                            ( phe5.posicao is not null AND phe5.posicao = 5 ) 
                            OR ( 
                                (phe5.posicao is null) 
                                AND
                                (phe5.ano = vhsa.ano_5serie AND phe5.nm_serie LIKE '%5%')
                            )
                        )
                    AND phe5.ano <= max_ano.ano
                    AND (CASE WHEN phe5.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe5.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd5 ON hd5.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd5.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd5.ref_sequencial = phe5.sequencial
                LEFT JOIN pmieducar.historico_escolar phe6 ON phe6.ref_cod_aluno = vhsa.cod_aluno AND phe6.ativo = 1 
                    AND (
                            (phe6.posicao is not null AND phe6.posicao = 6) 
                            OR (
                                (phe6.posicao is null)
                                AND
                                (phe6.ano = vhsa.ano_6serie AND phe6.nm_serie LIKE '%6%')
                            )
                        )
                    AND phe6.ano <= max_ano.ano
                    AND (CASE WHEN phe6.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe6.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd6 ON hd6.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND hd6.ref_sequencial = phe6.sequencial
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd6.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                LEFT JOIN pmieducar.historico_escolar phe7 ON phe7.ref_cod_aluno = vhsa.cod_aluno AND phe7.ativo = 1 
                    AND (
                            (phe7.posicao is not null AND phe7.posicao = 7)
                            OR (
                                (phe7.posicao is null)
                                AND
                                (phe7.ano = vhsa.ano_7serie AND phe7.nm_serie LIKE '%7%')
                            )
                        )
                    AND phe7.ano <= max_ano.ano
                    AND (CASE WHEN phe7.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe7.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd7 ON hd7.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND hd7.ref_sequencial = phe7.sequencial
                    AND TRIM(relatorio.get_texto_sem_caracter_especial(hd7.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                LEFT JOIN pmieducar.historico_escolar phe8 ON phe8.ref_cod_aluno = vhsa.cod_aluno AND phe8.ativo = 1 
                    AND (
                            (phe8.posicao is not null AND phe8.posicao = 8)
                            OR (
                                (phe8.posicao is null)
                                AND
                                (phe8.ano = vhsa.ano_8serie AND phe8.nm_serie LIKE '%8%')
                            )
                        )
                    AND phe8.ano <= max_ano.ano
                    AND (CASE WHEN phe8.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe8.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd8 ON hd8.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND hd8.ref_sequencial = phe8.sequencial
                    AND TRIM(relatorio.get_texto_sem_caracter_especial(hd8.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                LEFT JOIN pmieducar.historico_escolar phe9 ON phe9.ref_cod_aluno = vhsa.cod_aluno AND phe9.ativo = 1 
                    AND (
                            (phe9.posicao is not null AND phe9.posicao = 9)
                            OR (
                                (phe9.posicao is null)
                                AND
                                (phe9.ano = vhsa.ano_9serie AND phe9.nm_serie LIKE '%9%')
                            )
                        )
                    AND phe9.ano <= max_ano.ano
                    AND (CASE WHEN phe9.aprovado NOT IN (2,3,4,6,14,15) THEN 1=1 ELSE phe9.ref_cod_escola = $P{escola} END)
                LEFT JOIN pmieducar.historico_disciplinas hd9 ON hd9.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND hd9.ref_sequencial = phe9.sequencial
                    AND TRIM(relatorio.get_texto_sem_caracter_especial(hd9.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = aluno.cod_aluno)
                LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
                ORDER BY ordem_disciplina ASC
                ;
SQL;
    }
}