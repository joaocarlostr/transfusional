<?php
    include "database.php";
    include "function.php";

    function obterOpcoesDoBanco($conexao, $tabela, $nomeColuna, $valorColuna, $nomeColunaAdicional = ''){
        $opcoes  = "<option value=\"\">Selecione</option>";
        $query   = "SELECT $nomeColuna, $valorColuna";
        
        // Adicione a coluna adicional se especificada
        if (!empty($nomeColunaAdicional)) {
            $query .= ", $nomeColunaAdicional";
        }
    
        // Adicione a tabela Hemocomponentes usando JOIN, se for o caso
        if ($tabela == 'sth_Cadastro_Bolsa' && !empty($nomeColunaAdicional)) {
            $query = "SELECT $nomeColuna, cb.$valorColuna, c.id_bolsa as sth_Controle FROM $tabela cb
                        INNER JOIN sth_Hemocomponentes h ON cb.id_hemocomponente = h.id_hemocomponente
                        INNER JOIN sth_Controle c on c.id_bolsa = cb.id_bolsa
                        ORDER BY $nomeColuna";
        } else {
            $query .= " FROM $tabela";
        }

        if($tabela === "sth_Setores"){
            $query .= " WHERE $nomeColunaAdicional = 'ativo' ORDER BY $nomeColuna DESC";
        }

        if($tabela === "sth_setores"){
            $query .= " WHERE $nomeColunaAdicional = '' ORDER BY $nomeColuna DESC";
        }

        if($tabela == "sth_dados_paciente"){
            $query .= " ORDER BY $nomeColuna";
        }
    
        $result = conecta_query($conexao, $query);
    
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $nome = $row[$nomeColuna];
    
                //Adicione a descrição do hemocomponente se for o caso
                $descricao = isset($row['descricao']) ? $row['descricao'] . ' - ' : '';
                $valor     = $row[$valorColuna];
                $opcoes   .= "<option value=\"$valor\">$descricao$nome</option>";
            }
    
            pg_free_result($result);
        } else {
            die("Erro na consulta da tabela $tabela: " . pg_last_error($conexao));
        }
    
        return $opcoes;
    }    
    
    // Utilize a função para obter as opções dos setores e hemocomponentes
    $opcoesProntuario      = obterOpcoesDoBanco($conexao, "sth_dados_paciente", "prontuario", "id_paciente", "");
    $opcoesSetoresAtivos   = obterOpcoesDoBanco($conexao, 'sth_Setores', 'nome_setor', 'id_setor', 'status');
    $opcoesSetoresInativos = obterOpcoesDoBanco($conexao, 'sth_setores', 'nome_setor', 'id_setor', 'status');
    $opcoesBolsa           = obterOpcoesDoBanco($conexao, 'sth_Cadastro_Bolsa', 'num_bolsa', 'id_bolsa', 'descricao');
    $opcoesHemocomponentes = obterOpcoesDoBanco($conexao, 'sth_Hemocomponentes', 'sigla', 'id_hemocomponente', 'descricao');

    // Adicione o campo adicional 'iniciais' como exemplo. Ajuste conforme sua estrutura real.

    function obterOpcoesTipoReacao($conexao, $tipo){
        $opcoes = "";

        $query  = "SELECT id_reacao, cod, nome, descricao FROM sth_Tipos_Reacoes WHERE nome = '$tipo'";
        $result = conecta_query($conexao, $query);

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $id_reacao = $row['id_reacao'];
                $cod       = $row['cod'];
                $descricao = $row['descricao'];
                $opcoes   .= "<option value=\"$id_reacao\">$cod - $descricao</option>";
            }

            pg_free_result($result);
        } else {
            die("Erro na consulta da tabela Tipos_Reacoes: " . pg_last_error($conexao));
        }

        return $opcoes;
    }

    // Utilize a função para obter as opções dos tipos de reação
    $opcoesTipoReacaoImediata = obterOpcoesTipoReacao($conexao, "Reações Imediatas");
    $opcoesTipoReacaoTardia   = obterOpcoesTipoReacao($conexao, "Reações Tardias");

    $de_path_arquivo_txt    = null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>

    <title>Relatórios - HUM</title>
</head>
<body>
    <?php include_once "includes/header.php"; ?>

    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Relatórios</strong></p>
                <div id="fomulario" class="container-relatorio">
                 <!-- <img src="img/pagina_em_manutencao.png" alt="Página em manutenção imagem" width="900px" height="600px" > -->
                    <form action="gerar_relatorio.php" method="post" onsubmit="return validarFormulario()" enctype="multipart/form-data" >

                        <?php
                            if(isset($_SESSION['validado_relatorio']) && $_SESSION['validado_relatorio'] == 0){
                                exibir_mensagem_simples("Não foi possível gerar o relatório.", "Campos associados incorretamente.", "warning");
                            }

                            $_SESSION['validado_relatorio'] = 1;

                            if(isset($_SESSION['validado_relatorio_vazio']) && $_SESSION['validado_relatorio_vazio'] == 0){
                                exibir_mensagem_simples("Não foi possível gerar o relatório.", "Não há registros para esta requisição, relatório vazio.", "warning");
                            }

                            $_SESSION['validado_relatorio_vazio'] = 1;

                            if(isset($_SESSION['erro_arquivo'])){
                                
                                if($_SESSION['erro_arquivo'] == "erro no arquivo"){
                                    exibir_mensagem_simples("Não foi possível gerar o relatório.", "Erro na importação do arquivo.", "warning");
    
                                }else if($_SESSION['erro_arquivo'] == "nenhum arquivo enviado"){
                                    exibir_mensagem_simples("Não foi possível gerar o relatório.", "Nenhum arquivo enviado.", "warning");
    
                                }else if($_SESSION['erro_arquivo'] == "arquivo grande"){
                                    exibir_mensagem_simples("Não foi possível gerar o relatório.", "O arquivo enviado é muito grande.", "warning");
    
                                }else if($_SESSION['erro_arquivo'] == "arquivo tipo errado"){
                                    exibir_mensagem_simples("Não foi possível gerar o relatório.", "O tipo do arquivo enviado não é aceito para transferencia. Insira um arquivo .csv", "warning");
                                }
    
                                $_SESSION['erro_arquivo'] = -1;
                            }
                        ?>

                        <div class="row" >
                            <div class="col-lg-5" style="display:flex;" >
                                <div>
                                    <label for="data_inicio">Data de Início:</label><br>
                                    <input type="date" name="data_inicio" id="data_inicio">
                                </div>
                                <div style="margin-left:15px;" >
                                    <label for="data_fim">Data de Fim:</label><br>
                                    <input type="date" name="data_fim" id="data_fim" >
                                </div>
                            </div>
                            <div class="col-lg-7" >
                                <label for="tipo">Tipo de relatório (Opcional)</label><br>
                                <select name="tipo" id="tipo">
                                    <option value="">Selecione</option>
    
                                    <optgroup label="Bolsas">
                                        <option name="tipo" id="tipo_bolsa" value="bolsa">Bolsas transfundidas</option>
                                        <option name="tipo" id="tipo_bolsa_devolvida" value="bolsa_devolvida">Bolsas não transfundidas</option>
                                        <option name="tipo" id="bolsa_reserva" value="bolsa_reserva">Bolsas reserva</option>
                                        <option name="tipo" id="bolsa_repetida" value="bolsa_repetida">Bolsas repetidas</option>
                                    </optgroup>

                                    <optgroup label="Pacientes">
                                        <option name="tipo" id="tipo_paciente" value="paciente">Pacientes transfundidos</option>
                                        <option name="tipo" id="paciente_sem_registro" value="paciente_sem_registro">Pacientes sem registro</option>
                                        <option name="tipo" id="tipo_reacao_paciente" value="tipo_reacao_paciente">Reações por paciente</option>
                                    </optgroup>

                                    <optgroup label="Indicadores">
                                        <option name="tipo" id="indi_nao_conformidade" value="indi_nao_conformidade">Indicador de não conformidade</option>
                                        <option name="tipo" id="reacao_transfusional" value="reacao_transfusional">Indicador de reação transfusional</option>
                                        <option name="tipo" id="indi_bolsa_reserva" value="indi_bolsa_reserva">Indicador de bolsas reserva</option>
                                        <option name="tipo" id="indi_bolsa_devolvida" value="indi_bolsa_devolvida">Indicador de bolsas não transfundidas</option>
                                    </optgroup>

                                    <optgroup label="Outros">
                                        <option name="tipo" id="tipo_nao_conformidade" value="nao_conformidade">Não conformidade</option>
                                        <option name="tipo" id="tipo_sanguineo" value="tipo_sanguineo">Tipo sanguíneo</option>
                                        <option name="tipo" id="tipo_setor" value="tipo_setor">Setores</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="row" >
                            <div class="col-lg-5" >
                                <label for="id_setor">Setor:</label><br>
                                <select name="id_setor" style="width: 353px;" id="id_setor" >
                                    <optgroup label="Ativos" >
                                        <?php echo $opcoesSetoresAtivos; ?>
                                        <option value="pa_geral">PA - GERAL</option>
                                    </optgrou>
                                    <optgroup label="Inativos" >
                                        <?php echo $opcoesSetoresInativos; ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-lg-7">
                                <label for="hemocomponente">Hemocomponentes:</label><br>
                                <select name="hemocomponente" id="hemocomponente">
                                    <option value="">Selecione</option>
                                    <option value="todos">Todos</option>
                                    <?php //echo $opcoesHemocomponentes; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row"  >
                            <div class="col-lg-5">
                                <label for="bolsa">Bolsa:</label><br>
                                <select name="bolsa" id="bolsa" class="select2" style="width: 353px;">
                                    <?php echo $opcoesBolsa; ?>
                                </select>
                            </div>
                            <div class="col-lg-7" >
                                <label for="tipo_reacao">Tipo de Reação:</label><br>
                                <select name="tipo_reacao" id="tipo_reacao">
                                    <option value="">Selecione</option>
                                    <option value="todas">Todos</option>
                                </select>
                            </div>
                        </div>

                        <div class="row" >
                            <div class="col-lg-5">
                                <label for="prontuario">Prontuario:</label><br>
                                <select name="prontuario" id="prontuario" class="select2" style="width: 353px;">
                                    <?php echo $opcoesProntuario; ?>
                                </select>
                            </div>
                            <div class="col-lg-7" >
                                <label for="importa_arquivo">Comparar bolsas transfundidas: (insira um arquivo .csv)</label>
                                <input type="hidden" name="MAX_SIZE_FILE" value="10000000">
                                <input type="file" name="importa_arquivo" id="importa_arquivo" style="width: 544px;" accept=".csv">
                            </div>
                        </div>

                        <div class="botoes-relatorio">
                            <input type="submit" value="Gerar Relatório" class="btn botao-verde" name="gerar_relatorio">
                            <input type="reset"  value="Limpar Campos" class="btn botao-limpar" onclick="limpa_select2()">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Botão flutuante de ajuda -->
    <div class="floating-button" id="helpButton">
        <i class="fas fa-question"></i>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Relatório</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div style="height:500px; overflow:scroll;">
                        <!-- Conteúdo da primeira ajuda -->
                        <h5 style="font-weight: bold;">BOLSAS</h5><hr>
                        <p style="font-weight: bold;">Relatório de bolsas Transfundidas: </p>
                        <ul>
                            <li>Selecione a opção "bolsas transfundidas" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data fim, e/ou um setor e/ou uma bolsa e/ou um prontuário.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de bolsas não transfundidas: </p>
                        <ul>
                            <li>Selecione a opção "bolsas não transfundidas" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim e/ou uma bolsa.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de bolsa reserva: </p>
                        <ul>
                            <li>Selecione a opção "Bolsa reserva" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data fim, e/ou um setor e/ou uma bolsa.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de bolsa repetida: </p>
                        <ul>
                            <li>Selecione a opção "Bolsa repetida" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione um prontuário, e/ou um setor e/ou uma bolsa.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório comparação com hemocentro: </p>
                        <ul>
                            <li>Tenha o relatório em excel do hemocentro</li>
                            <li><a href="https://www.freeconvert.com/xlsx-to-csv">Coverta para .csv</a>
                                <ul>
                                    <li>Selecione o arquivo excel -> Choose files</li>
                                    <li>Clique em Convert</li>
                                    <li>Clique em download</li>
                                </ul>
                            </li>
                            <li>Clique em escolher arquivo e selecione o arquivo convertido para .csv</li>
                            <li>Selecione uma data início e uma data fim</li>
                            <li>Clique em gerar relatório</li>
                        </ul>

                        <h5 style="font-weight: bold;">PACIENTES</h5><hr>
                        <p style="font-weight: bold;">Relatório de Pacientes Transfundidos: </p>
                        <ul>
                            <li>Selecione a opção "Pacientes" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim e/ou um setor, e/ou uma bolsa.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de Pacientes sem registro: </p>
                        <ul>
                            <li>Selecione a opção "Pacientes sem registro" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Não há a opção de filtros para este relatório.</li>
                        </ul>

                        <h5 style="font-weight: bold;">INDICADORES</h5><hr>
                        <p style="font-weight: bold;">Relatório de indicador de reações transfusionais: </p>
                        <ul>
                            <li>Selecione a opção "indicador de reação transfusional" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de indicador de não conformidades: </p>
                        <ul>
                            <li>Selecione a opção "indicador de não conformidade" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de indicador de bolsas não transfundidas: </p>
                        <ul>
                            <li>Selecione a opção "indicador de bolsa não transfundida" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de indicador de bolsas reserva: </p>
                        <ul>
                            <li>Selecione a opção "indicador de bolsas reserva" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim.</li>
                        </ul>

                        <h5 style="font-weight: bold;">OUTROS</h5><hr>
                        <p style="font-weight: bold;">Relatório de tipo sanguíneo: </p>
                        <ul>
                            <li>Selecione a opção "Tipo sanguíneo" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de não conformidade: </p>
                        <ul>
                            <li>Selecione a opção "Não conformidade" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim e/ou um setor.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de setores: </p>
                        <ul>
                            <li>Selecione a opção "Setores" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim e/ou um setor.</li>
                        </ul>

                        <p style="font-weight: bold;">Relatório de reações transfusionais por paciente: </p>
                        <ul>
                            <li>Selecione a opção "Reações por paciente" no campo Tipo de relatório e aperte no botão Gerar relatório.</li>
                            <li>Caso deseje filtrar, selecione uma data de início e uma data de fim e/ou um setor.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>
    
    <script>
        function validarFormulario() {
            var dt_inicio       = document.getElementById('data_inicio').value;
            var dt_fim          = document.getElementById('data_fim').value;
            var tipo            = document.getElementById('tipo').value;
            var setor           = document.getElementById('id_setor').value;
            var bolsa           = document.getElementById('bolsa').value;
            var hemocomponente  = document.getElementById('hemocomponente').value;
            var reacao          = document.getElementById('tipo_reacao').value;
            var importa_arquivo = document.getElementById('importa_arquivo').value;
            var prontuario      = document.getElementById('prontuario').value;

            if (!dt_inicio && !dt_fim && !setor && !bolsa && !hemocomponente && !reacao && !tipo && !importa_arquivo && !prontuario) {
                alert('Por favor, selecione uma opção.');
                return false;
            }
            
            return true;
        }
        
        $(document).ready(function() {
            $(".select2").select2();
        });

        document.addEventListener('DOMContentLoaded', function () {
            const form           = document.querySelector('form');
            const dataInicio     = form.querySelector('[name="data_inicio"]');
            const dataFim        = form.querySelector('[name="data_fim"]');
            const intervaloInput = form.querySelector('[name="intervalo"]');

            form.addEventListener('submit', function (event) {
                // Adicione o intervalo de datas ao campo hidden
                const intervalo      = construirIntervalo();
                intervaloInput.value = intervalo;
            });

            function construirIntervalo() {
                const inicio = dataInicio.value.trim();
                const fim    = dataFim.value.trim();

                if (inicio && fim) {
                    return `${inicio} - ${fim}`;
                } else if (inicio) {
                    return `${inicio} - ${inicio}`;
                } else if (fim) {
                    return `${fim} - ${fim}`;
                }

                // Se nenhum dos casos acima, retornar uma string vazia
                return '';
            }
        });

        //volta ao index 0 do select2
        function limpa_select2() {
            $('.select2').val(null).trigger('change');
        }
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    
</body>
</html>