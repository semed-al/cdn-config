<?php

trait SchoolHistorySeriesYearsTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $escola = $this->args['escola'];
        $aluno = $this->args['aluno'];
        $nao_emitir_reprovado = $this->args['nao_emitir_reprovado'];

        return <<<SQL
            SELECT
                vhsa.cod_aluno,
                trim(vhsa.disciplina) AS nm_disciplina,
                pessoa.nome AS nome_aluno,
                eca.cod_aluno_inep AS cod_inep,
                municipio.nome || ', Estado de ' || municipio.sigla_uf AS cidade_nascimento_uf,
                municipio.sigla_uf AS uf_nascimento,
                municipio.nome AS cidade_nascimento,
                to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
                INITCAP(relatorio.get_pai_aluno(vhsa.cod_aluno)) AS nome_do_pai,
                INITCAP(relatorio.get_mae_aluno(vhsa.cod_aluno)) AS nome_da_mae,
                COALESCE(vhsa.carga_horaria_disciplina1, hd1.carga_horaria_disciplina) AS chd1,
                COALESCE(vhsa.carga_horaria_disciplina2, hd2.carga_horaria_disciplina) AS chd2,
                COALESCE(vhsa.carga_horaria_disciplina3, hd3.carga_horaria_disciplina) AS chd3,
                COALESCE(vhsa.carga_horaria_disciplina4, hd4.carga_horaria_disciplina) AS chd4,
                COALESCE(vhsa.carga_horaria_disciplina5, hd5.carga_horaria_disciplina) AS chd5,
                COALESCE(vhsa.carga_horaria_disciplina6, hd6.carga_horaria_disciplina) AS chd6,
                COALESCE(vhsa.carga_horaria_disciplina7, hd7.carga_horaria_disciplina) AS chd7,
                COALESCE(vhsa.carga_horaria_disciplina8, hd8.carga_horaria_disciplina) AS chd8,
                COALESCE(vhsa.carga_horaria_disciplina9, hd9.carga_horaria_disciplina) AS chd9,
                COALESCE(vhsa.status_serie1, relatorio.get_situacao_historico_abreviado(phe1.aprovado)) AS status_serie1,
                COALESCE(vhsa.status_serie2, relatorio.get_situacao_historico_abreviado(phe2.aprovado)) AS status_serie2,
                COALESCE(vhsa.status_serie3, relatorio.get_situacao_historico_abreviado(phe3.aprovado)) AS status_serie3,
                COALESCE(vhsa.status_serie4, relatorio.get_situacao_historico_abreviado(phe4.aprovado)) AS status_serie4,
                COALESCE(vhsa.status_serie5, relatorio.get_situacao_historico_abreviado(phe5.aprovado)) AS status_serie5,
                COALESCE(vhsa.status_serie6, relatorio.get_situacao_historico_abreviado(phe6.aprovado)) AS status_serie6,
                COALESCE(vhsa.status_serie7, relatorio.get_situacao_historico_abreviado(phe7.aprovado)) AS status_serie7,
                COALESCE(vhsa.status_serie8, relatorio.get_situacao_historico_abreviado(phe8.aprovado)) AS status_serie8,
                COALESCE(vhsa.status_serie9, relatorio.get_situacao_historico_abreviado(phe9.aprovado)) AS status_serie9,
                COALESCE(vhsa.carga_horaria1, phe1.carga_horaria) AS carga_horaria1,
                COALESCE(vhsa.carga_horaria2, phe2.carga_horaria) AS carga_horaria2,
                COALESCE(vhsa.carga_horaria3, phe3.carga_horaria) AS carga_horaria3,
                COALESCE(vhsa.carga_horaria4, phe4.carga_horaria) AS carga_horaria4,
                COALESCE(vhsa.carga_horaria5, phe5.carga_horaria) AS carga_horaria5,
                COALESCE(vhsa.carga_horaria6, phe6.carga_horaria) AS carga_horaria6,
                COALESCE(vhsa.carga_horaria7, phe7.carga_horaria) AS carga_horaria7,
                COALESCE(vhsa.carga_horaria8, phe8.carga_horaria) AS carga_horaria8,
                COALESCE(vhsa.carga_horaria9, phe9.carga_horaria) AS carga_horaria9,
                COALESCE(vhsa.frequencia1, phe1.frequencia) AS freq1,
                COALESCE(vhsa.frequencia2, phe2.frequencia) AS freq2,
                COALESCE(vhsa.frequencia3, phe3.frequencia) AS freq3,
                COALESCE(vhsa.frequencia4, phe4.frequencia) AS freq4,
                COALESCE(vhsa.frequencia5, phe5.frequencia) AS freq5,
                COALESCE(vhsa.frequencia6, phe6.frequencia) AS freq6,
                COALESCE(vhsa.frequencia7, phe7.frequencia) AS freq7,
                COALESCE(vhsa.frequencia8, phe8.frequencia) AS freq8,
                COALESCE(vhsa.frequencia9, phe9.frequencia) AS freq9,
                COALESCE(vhsa.nota_1serie, hd1.nota) AS nota_1serie,
                COALESCE(vhsa.nota_2serie, hd2.nota) AS nota_2serie,
                COALESCE(vhsa.nota_3serie, hd3.nota) AS nota_3serie,
                COALESCE(vhsa.nota_4serie, hd4.nota) AS nota_4serie,
                COALESCE(vhsa.nota_5serie, hd5.nota) AS nota_5serie,
                COALESCE(vhsa.nota_6serie, hd6.nota) AS nota_6serie,
                COALESCE(vhsa.nota_7serie, hd7.nota) AS nota_7serie,
                COALESCE(vhsa.nota_8serie, hd8.nota) AS nota_8serie,
                COALESCE(vhsa.nota_9serie, hd9.nota) AS nota_9serie,
                COALESCE(vhsa.ano_1serie, phe1.ano) AS ano_1serie,
                COALESCE(vhsa.ano_2serie, phe2.ano) AS ano_2serie,
                COALESCE(vhsa.ano_3serie, phe3.ano) AS ano_3serie,
                COALESCE(vhsa.ano_4serie, phe4.ano) AS ano_4serie,
                COALESCE(vhsa.ano_5serie, phe5.ano) AS ano_5serie,
                COALESCE(vhsa.ano_6serie, phe6.ano) AS ano_6serie,
                COALESCE(vhsa.ano_7serie, phe7.ano) AS ano_7serie,
                COALESCE(vhsa.ano_8serie, phe8.ano) AS ano_8serie,
                COALESCE(vhsa.ano_9serie, phe9.ano) AS ano_9serie,
                COALESCE(vhsa.escola_1serie, phe1.escola) AS escola_1serie,
                COALESCE(vhsa.escola_2serie, phe2.escola) AS escola_2serie,
                COALESCE(vhsa.escola_3serie, phe3.escola) AS escola_3serie,
                COALESCE(vhsa.escola_4serie, phe4.escola) AS escola_4serie,
                COALESCE(vhsa.escola_5serie, phe5.escola) AS escola_5serie,
                COALESCE(vhsa.escola_6serie, phe6.escola) AS escola_6serie,
                COALESCE(vhsa.escola_7serie, phe7.escola) AS escola_7serie,
                COALESCE(vhsa.escola_8serie, phe8.escola) AS escola_8serie,
                COALESCE(vhsa.escola_9serie, phe9.escola) AS escola_9serie,
                COALESCE(vhsa.escola_uf_1serie, phe1.escola_uf) AS escola_uf_1serie,
                COALESCE(vhsa.escola_uf_2serie, phe2.escola_uf) AS escola_uf_2serie,
                COALESCE(vhsa.escola_uf_3serie, phe3.escola_uf) AS escola_uf_3serie,
                COALESCE(vhsa.escola_uf_4serie, phe4.escola_uf) AS escola_uf_4serie,
                COALESCE(vhsa.escola_uf_5serie, phe5.escola_uf) AS escola_uf_5serie,
                COALESCE(vhsa.escola_uf_6serie, phe6.escola_uf) AS escola_uf_6serie,
                COALESCE(vhsa.escola_uf_7serie, phe7.escola_uf) AS escola_uf_7serie,
                COALESCE(vhsa.escola_uf_8serie, phe8.escola_uf) AS escola_uf_8serie,
                COALESCE(vhsa.escola_uf_9serie, phe9.escola_uf) AS escola_uf_9serie,
                COALESCE(vhsa.escola_cidade_1serie, phe1.escola_cidade) AS escola_cidade_1serie,
                COALESCE(vhsa.escola_cidade_2serie, phe2.escola_cidade) AS escola_cidade_2serie,
                COALESCE(vhsa.escola_cidade_3serie, phe3.escola_cidade) AS escola_cidade_3serie,
                COALESCE(vhsa.escola_cidade_4serie, phe4.escola_cidade) AS escola_cidade_4serie,
                COALESCE(vhsa.escola_cidade_5serie, phe5.escola_cidade) AS escola_cidade_5serie,
                COALESCE(vhsa.escola_cidade_6serie, phe6.escola_cidade) AS escola_cidade_6serie,
                COALESCE(vhsa.escola_cidade_7serie, phe7.escola_cidade) AS escola_cidade_7serie,
                COALESCE(vhsa.escola_cidade_8serie, phe8.escola_cidade) AS escola_cidade_8serie,
                COALESCE(vhsa.escola_cidade_9serie, phe9.escola_cidade) AS escola_cidade_9serie,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1
                    AND ((phe.posicao is not null AND phe.posicao = 1) OR (phe.ano = vhsa.ano_1serie AND phe.nm_serie LIKE '%1%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie1,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 2) OR (phe.ano = vhsa.ano_2serie AND phe.nm_serie LIKE '%2%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie2,
                (
                    SELECT CASE 
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 3) OR (phe.ano = vhsa.ano_3serie AND phe.nm_serie LIKE '%3%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie3,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 4) OR (phe.ano = vhsa.ano_4serie AND phe.nm_serie LIKE '%4%'))              
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie4,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 5) OR (phe.ano = vhsa.ano_5serie AND phe.nm_serie LIKE '%5%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie5,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 6) OR (phe.ano = vhsa.ano_6serie AND phe.nm_serie LIKE '%6%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie6,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 7) OR (phe.ano = vhsa.ano_7serie AND phe.nm_serie LIKE '%7%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie7,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 8) OR (phe.ano = vhsa.ano_8serie AND phe.nm_serie LIKE '%8%'))
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie8,
                (
                    SELECT CASE
                        WHEN phe.historico_grade_curso_id = 1 AND LOWER(phe.nm_serie) NOT LIKE '%série%' THEN CONCAT(phe.nm_serie,' Série')
                        WHEN phe.historico_grade_curso_id = 2 AND LOWER(phe.nm_serie) NOT LIKE '%ano%' THEN CONCAT(phe.nm_serie,' Ano')
                        ELSE phe.nm_serie 
                    END
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                    AND phe.ativo = 1  
                    AND ((phe.posicao is not null AND phe.posicao = 9) OR (phe.ano = vhsa.ano_9serie AND phe.nm_serie LIKE '%9%')) 
                    ORDER BY phe.ano DESC LIMIT 1
                ) AS nome_serie9,
                (
                    SELECT municipio
                    FROM relatorio.view_dados_escola
                    WHERE cod_escola = $escola
                ) AS municipio,
                (
                    SELECT fcn_upper(p.nome)
                    FROM cadastro.pessoa p
                    INNER JOIN pmieducar.escola e ON (e.ref_idpes_gestor = p.idpes)
                    WHERE e.cod_escola = $escola
                ) AS diretor,
                (
                    SELECT fcn_upper(p.nome)
                    FROM cadastro.pessoa p
                    INNER JOIN pmieducar.escola e ON (p.idpes = e.ref_idpes_secretario_escolar)
                    WHERE e.cod_escola = $escola
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
                            WHEN he.aprovado = 3 THEN 'está cursando '
                            ELSE 'concluiu '
                        END || (
                                CASE
                                    WHEN (
                                        SELECT substring(nm_serie,1,1)::integer
                                        FROM pmieducar.historico_escolar he
                                        WHERE he.ref_cod_aluno = vhsa.cod_aluno
                                        AND he.ativo = 1
                                        AND he.historico_grade_curso_id = 2
                                        ORDER BY he.ano DESC LIMIT 1
                                    ) = 1 
                                    THEN 'o ' || (substring(nm_serie,1,1)::integer::numeric) || 'º ano'
                                    ELSE
                                        CASE 
                                            WHEN historico_grade_curso_id = 1 
                                            THEN
                                                CASE
                                                    WHEN substring(nm_serie,1,1)::integer = '0' 
                                                    THEN 'o ' || (substring(nm_serie,1,1)::integer::numeric +1) || 'º ano' || ' do Ensino Fundamental'
                                                    ELSE 'a ' || substring(nm_serie,1,1)::integer || 'ª série/' || (substring(nm_serie,1,1)::integer::numeric +1) || 'º ano' || ' do Ensino Fundamental'
                                                END
                                            WHEN historico_grade_curso_id = 2
                                            THEN
                                                CASE
                                                    WHEN (substring(nm_serie,1,1)::integer::numeric -1) = '0' 
                                                    THEN 'o ' || substring(nm_serie,1,1)::integer || 'º ano' || ' do Ensino Fundamental'
                                                    ELSE 'a ' || (substring(nm_serie,1,1)::integer::numeric -1) || 'ª série/' || substring(nm_serie,1,1)::integer || 'º ano' || ' do Ensino Fundamental'
                                                END
                                            ELSE 
                                                nm_serie || ' da Educação de Jovens e Adultos (EJA)'
                                        END
                                END
                            )
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.aprovado NOT IN (2,3,4,6)
                    AND he.extra_curricular = 0
                    AND ativo = 1
                    ORDER BY ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                    LIMIT 1
                ) AS nome_serie_aux,
                (
                    SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.aprovado NOT IN (2,3,4,6)
                    AND he.extra_curricular = 0
                    AND ativo = 1
                    ORDER BY he.ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                    LIMIT 1
                ) AS ano,
                (
                    SELECT count(hd.nota)
                    FROM pmieducar.historico_disciplinas hd
                    INNER JOIN pmieducar.historico_escolar he ON (he.ref_cod_aluno = hd.ref_ref_cod_aluno
                        AND he.sequencial = hd.ref_sequencial)
                    WHERE hd.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND
                        CASE
                            WHEN isnumeric(replace(hd.nota, ',', '.')) THEN replace(hd.nota, ',', '.')::float > 10
                            ELSE FALSE
                        END
                ) AS qtde_notas_maiores_dez,
                (
                    SELECT DISTINCT '' || (replace(textcat_all((' ')),' <br> ','<br>'))
                    FROM generate_series(1,(
                        SELECT ROUND((330 - (COUNT(DISTINCT trim(relatorio.get_texto_sem_caracter_especial(nm_disciplina))) * 12)) / 12)
                        FROM historico_disciplinas hd
                        INNER JOIN pmieducar.historico_escolar he ON (he.ref_cod_aluno = hd.ref_ref_cod_aluno
                            AND hd.ref_sequencial = he.sequencial)
                        WHERE ref_ref_cod_aluno = vhsa.cod_aluno
                    )::INTEGER)
                ) AS espaco_branco,
                (
                    SELECT COUNT(1)
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.ativo = 1
                    AND (CASE WHEN $nao_emitir_reprovado THEN he.aprovado <> 2 ELSE 1=1 END)
                    AND he.dependencia = 't'
                ) AS possui_historico_dependencia,

                (
                    SELECT textcat_all(obs)
                    FROM (
                        SELECT concat(observacao,'<br>') AS obs
                        FROM pmieducar.historico_escolar phe
                        WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                        AND phe.ativo = 1
                        AND LENGTH(observacao) > 0
                        AND (CASE WHEN $nao_emitir_reprovado THEN phe.aprovado <> 2 ELSE 1=1 END)                        
                        ORDER BY phe.ano
                    )tabl
                ) AS observacao_all,
                (
                    SELECT area.nome
                    FROM modules.area_conhecimento area
                    LEFT JOIN relatorio.view_componente_curricular vcc ON (area.id = vcc.area_conhecimento_id)
                    WHERE relatorio.get_texto_sem_caracter_especial(vcc.nome) = vhsa.disciplina
                    LIMIT 1
                ) AS ac_nome,
                (
                    SELECT cc.ordenamento 
                    FROM modules.componente_curricular cc 
                    WHERE relatorio.get_texto_sem_caracter_especial(cc.nome) = vhsa.disciplina
                    ORDER BY cc.ordenamento ASC
                    LIMIT 1
                ) AS ordem_disciplina
                FROM relatorio.view_historico_series_anos vhsa
                INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = vhsa.cod_aluno)
                INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
                LEFT JOIN pmieducar.historico_disciplinas hd1 ON hd1.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd1.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd1.ref_sequencial = 1
                LEFT JOIN pmieducar.historico_disciplinas hd2 ON hd2.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd2.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd2.ref_sequencial = 2
                LEFT JOIN pmieducar.historico_disciplinas hd3 ON hd3.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd3.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd3.ref_sequencial = 3
                LEFT JOIN pmieducar.historico_disciplinas hd4 ON hd4.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd4.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd4.ref_sequencial = 4
                LEFT JOIN pmieducar.historico_disciplinas hd5 ON hd5.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd5.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd5.ref_sequencial = 5
                LEFT JOIN pmieducar.historico_disciplinas hd6 ON hd6.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND trim(relatorio.get_texto_sem_caracter_especial(hd6.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd6.ref_sequencial = 6
                LEFT JOIN pmieducar.historico_disciplinas hd7 ON hd7.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND TRIM(relatorio.get_texto_sem_caracter_especial(hd7.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd7.ref_sequencial = 7
                LEFT JOIN pmieducar.historico_disciplinas hd8 ON hd8.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND TRIM(relatorio.get_texto_sem_caracter_especial(hd8.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd8.ref_sequencial = 8
                LEFT JOIN pmieducar.historico_disciplinas hd9 ON hd9.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND TRIM(relatorio.get_texto_sem_caracter_especial(hd9.nm_disciplina)) = trim(relatorio.get_texto_sem_caracter_especial(vhsa.disciplina)) 
                    AND hd9.ref_sequencial = 9
                LEFT JOIN pmieducar.historico_escolar phe1 ON phe1.ref_cod_aluno = vhsa.cod_aluno AND phe1.ativo = 1 
                    AND ((phe1.posicao is not null AND phe1.posicao = 1) OR (phe1.ano = vhsa.ano_1serie AND phe1.nm_serie LIKE '%1%'))
                LEFT JOIN pmieducar.historico_escolar phe2 ON phe2.ref_cod_aluno = vhsa.cod_aluno AND phe2.ativo = 1 
                    AND ((phe2.posicao is not null AND phe2.posicao = 2) OR (phe2.ano = vhsa.ano_2serie AND phe2.nm_serie LIKE '%2%'))
                LEFT JOIN pmieducar.historico_escolar phe3 ON phe3.ref_cod_aluno = vhsa.cod_aluno AND phe3.ativo = 1 
                    AND ((phe3.posicao is not null AND phe3.posicao = 3) OR (phe3.ano = vhsa.ano_3serie AND phe3.nm_serie LIKE '%3%'))
                LEFT JOIN pmieducar.historico_escolar phe4 ON phe4.ref_cod_aluno = vhsa.cod_aluno AND phe4.ativo = 1 
                    AND ((phe4.posicao is not null AND phe4.posicao = 4) OR (phe4.ano = vhsa.ano_4serie AND phe4.nm_serie LIKE '%4%'))
                LEFT JOIN pmieducar.historico_escolar phe5 ON phe5.ref_cod_aluno = vhsa.cod_aluno AND phe5.ativo = 1 
                    AND ((phe5.posicao is not null AND phe5.posicao = 5) OR (phe5.ano = vhsa.ano_5serie AND phe5.nm_serie LIKE '%5%'))
                LEFT JOIN pmieducar.historico_escolar phe6 ON phe6.ref_cod_aluno = vhsa.cod_aluno AND phe6.ativo = 6 
                    AND ((phe6.posicao is not null AND phe6.posicao = 6) OR (phe6.ano = vhsa.ano_6serie AND phe6.nm_serie LIKE '%6%'))
                LEFT JOIN pmieducar.historico_escolar phe7 ON phe7.ref_cod_aluno = vhsa.cod_aluno AND phe7.ativo = 7 
                    AND ((phe7.posicao is not null AND phe7.posicao = 7) OR (phe7.ano = vhsa.ano_7serie AND phe7.nm_serie LIKE '%7%'))
                LEFT JOIN pmieducar.historico_escolar phe8 ON phe8.ref_cod_aluno = vhsa.cod_aluno AND phe8.ativo = 1 
                    AND ((phe8.posicao is not null AND phe8.posicao = 8) OR (phe8.ano = vhsa.ano_8serie AND phe8.nm_serie LIKE '%8%'))
                LEFT JOIN pmieducar.historico_escolar phe9 ON phe9.ref_cod_aluno = vhsa.cod_aluno AND phe9.ativo = 1 
                    AND ((phe9.posicao is not null AND phe9.posicao = 9) OR (phe9.ano = vhsa.ano_9serie AND phe9.nm_serie LIKE '%9%'))
                LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = aluno.cod_aluno)
                LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
                WHERE vhsa.cod_aluno = $aluno
                ORDER BY ordem_disciplina ASC
                ;

SQL;
    }
}
