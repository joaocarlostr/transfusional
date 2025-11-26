<?php
    include "database.php";
    include "function.php";

    $id_paciente_antigo = isset($_SESSION["unificar_id_paciente_antigo"]) ? (int) $_SESSION["unificar_id_paciente_antigo"] : 0;
    $pacientesPorPagina = 12;

    //inicializando variaveis
    $nome_completo = $nome_mae = $cpf = $dt_nasc = $dt_requisicao = $nome_setor = $prontuario = $registro = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome_completo = isset($_POST['nome_completo']) ? $_POST['nome_completo'] : '';
        $nome_mae      = isset($_POST['nome_mae'])      ? $_POST['nome_mae']      : '';
        $cpf           = isset($_POST['cpf'])           ? $_POST['cpf']           : '';
        $dt_nasc       = isset($_POST['dt_nasc'])       ? $_POST['dt_nasc']       : '';
        $dt_requisicao = isset($_POST['dt_requisicao']) ? $_POST['dt_requisicao'] : '';
        $nome_setor    = isset($_POST['nome_setor'])    ? $_POST['nome_setor']    : '';
        $prontuario    = isset($_POST['prontuario'])    ? $_POST['prontuario']    : '';
        $registro      = isset($_POST['registro'])      ? $_POST['registro']      : '';
    }

    //conta a qtd de pacientes
    $query_qtd_paciente  = "SELECT count(id_paciente) as qtd_paciente FROM sth_dados_paciente";
    $result_qtd_paciente = conecta_query($conexao, $query_qtd_paciente);
    $row_qtd_paciente    = pg_fetch_assoc($result_qtd_paciente);
    $total_paciente      = (int) $row_qtd_paciente["qtd_paciente"];

    $totalPaginas = ceil($total_paciente / $pacientesPorPagina);

    $paginaAtual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
    $offset      = ($paginaAtual - 1) * $pacientesPorPagina;

    //busca setores ativos
    $query_setores_ativo  = "SELECT nome_setor FROM sth_setores WHERE status='ativo' ORDER BY nome_setor DESC";
    $result_setores_ativo = conecta_query($conexao, $query_setores_ativo);

    //busca setores inativos
    $query_setores_inativo  = "SELECT nome_setor FROM sth_setores WHERE status='' ORDER BY nome_setor DESC";
    $result_setores_inativo = conecta_query($conexao, $query_setores_inativo);

    //busca dados do paciente
    $query_paciente = "SELECT p.nome_completo, nome_social, nome_mae, prontuario, registro, dt_nasc, cpf, dt_requisicao, id_paciente, s.nome_setor,
    CASE WHEN p.nome_social is null or p.nome_social = '' then p.nome_completo ELSE p.nome_social END as nome 
    FROM sth_dados_paciente p
    INNER JOIN sth_setores s ON p.id_setor = s.id_setor
    WHERE 1=1";

    //query paciente para unificar
    $query_paciente_unificar = "$query_paciente AND p.id_paciente != $id_paciente_antigo ORDER BY nome";

    //dados do paciente
    if (!empty($nome_completo)) {
        $query_paciente .= " AND nome_completo LIKE '%$nome_completo%' OR nome_social LIKE '%$nome_completo%'";
    }

    if (!empty($nome_mae)) {
        $query_paciente .= " AND nome_mae LIKE '%$nome_mae%'";
    }

    if (!empty($prontuario)) {
        $query_paciente .= " AND prontuario LIKE '%$prontuario%'";
    }

    if (!empty($nome_setor)) {
        $query_paciente .= " AND p.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor = '$nome_setor')";
    }

    if (!empty($dt_nasc)) {
        $dt_nasc = date('Y-m-d', strtotime($dt_nasc));
        $query_paciente .= " AND DATE(dt_nasc) = '$dt_nasc'";
    }

    if (!empty($cpf)) {
        $query_paciente .= " AND cpf LIKE '%$cpf%'";
    }

    if (!empty($dt_requisicao)) {
        $dt_requisicao   = date('Y-m-d', strtotime($dt_requisicao));
        $query_paciente .= " AND DATE(dt_requisicao) = '$dt_requisicao'";
    }

    if (!empty($registro)) {
        $query_paciente .= " AND p.registro LIKE '%$registro%'";
    }

    //paciente que aparecem na busca
    $query_paciente          .= " ORDER BY p.dt_requisicao, nome LIMIT $pacientesPorPagina OFFSET $offset";
    $result_paciente          = conecta_query($conexao, $query_paciente);
    $result_paciente_unificar = conecta_query($conexao, $query_paciente_unificar);
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
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="css/style.css">
    
    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    
    <title>Buscar paciente - HUM</title>
</head>

<body>
    <?php include_once "includes/header.php"; ?>
    <div class="container-pacientes" id="container-pacientes">
        <div class="fundo-imagem">
            <div id="corpo-pacientes" class="borda-pacientes">
                <p class="mensagem-borda"><strong>Buscar Paciente</strong></p>
                <!-- Formulário de busca -->
                <div class="pesquisa">
                    <form id="formulario-pesquisa" method="POST" action="buscar_paciente.php">
                        <p class="legenda"><strong>Pesquisar Paciente:</strong></p>

                        <?php
                            if(isset($_SESSION['unificar'])){
                                if ($_SESSION['unificar'] == 1) {
                                    echo "<script>
                                                Swal.fire({
                                                    title: 'Atenção!',
                                                    text: 'Deseja excluir este paciente?.',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'Sim, excluir',
                                                    cancelButtonText: 'Cancelar'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.href = 'unificar.php?unificar_resposta=2';
                                                    }
                                                });
                                            </script>";    
                                }else if ($_SESSION['unificar'] == 2) {
                                    echo "<script>
                                                Swal.fire({
                                                    title: 'Atenção!',
                                                    text: 'Este paciente possui bolsas vinculadas.',
                                                    icon: 'warning',
                                                    confirmButtonColor: '#3085d6',
                                                    confirmButtonText: 'Visualizar',
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $('#paciente_antigo_unificar_dados').modal('show');
                                                    }
                                                });
                                            </script>";    
                                }else if ($_SESSION['unificar'] == 3) {
                                    exibir_mensagem_simples("Paciente unificado.", "Seu paciente foi unificado com sucesso.", "success");
    
                                }else if ($_SESSION['unificar'] == 4) {
                                    exibir_mensagem_simples("Paciente excluido.", "Seu paciente foi excluido com sucesso.", "success");
      
                                }else if ($_SESSION['unificar'] == 5) {
                                    echo "<script>
                                                Swal.fire({
                                                    title: 'Atenção!',
                                                    text: 'Confirmar a escolha do paciente?',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'Sim, confirmar',
                                                    cancelButtonText: 'Cancelar'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $('#paciente_unificar_dados').modal('show');
                                                    }else{
                                                        $('#selecionado_paciente_unificar').modal('show');
                                                    }
                                                });
                                            </script>";   
                                }

                                $_SESSION['unificar'] = -1;
                            }        
                        ?>

                        <div class="row" >
                            <div class="col-lg-12" style="display: flex;">
                                <div>
                                    <label for="nome_completo">Nome Paciente:</label><br>
                                    <input type="text" size="50" name="nome_completo" id="nome_completo" onClick="this.select();" maxlength="255" placeholder="Digite as iniciais, nome ou sobrenome">
                                </div>
                                <div>
                                    <label for="cpf">CPF:</label><br>
                                    <input type="text" size="30" name="cpf" id="cpf" placeholder="000.000.000-00" oninput="formatarCPF(this)" onblur="validarCPF(this)" maxlength="14">
                                </div>
                                <div>
                                    <label for="prontuario">Prontuário:</label><br>
                                    <input type="text" size="25" name="prontuario" id="prontuario" oninput="apenasNumeros(this)" maxlength="15" >
                                </div>
                                <div>
                                    <label for="registro">Registro:</label><br>
                                    <input type="text" size="15" name="registro" id="registro" oninput="apenasNumeros(this)" maxlength="15" >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campos de pesquisa avançada (inicialmente ocultos) -->
                        <div id="campos-avancados" class="campos-avancados" style="display: none">
                            <div class="row" >
                                <div class="col-lg-12" style="display: flex;">
                                    <div>
                                        <label for="nome_mae">Nome da Mãe:</label><br>
                                        <input type="text" size="50" name="nome_mae" id="nome_mae" maxlength="255">
                                    </div>
                                    <div>
                                        <label for="dt_nasc">Data de Nascimento:</label><br>
                                        <input type="date" name="dt_nasc" id="dt_nasc" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div >
                                        <label for="dt_requisicao">Data de Cadastro:</label><br>
                                        <input type="date" name="dt_requisicao" id="dt_requisicao" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div>
                                        <label for="nome_setor">Setor:</label><br>
                                        <select name="nome_setor" id="nome_setor">
                                            <option value="">Selecione o Setor</option>
                                            <optgroup label="Ativos" >
                                                <?php
                                                    if ($result_setores_ativo && pg_num_rows($result_setores_ativo) > 0) {
                                                        while ($row_setor = pg_fetch_assoc($result_setores_ativo)) {
                                                            echo "<option value='$row_setor[nome_setor]'> $row_setor[nome_setor] </option>"; 
                                                            // cuidado com espaços depois e antes das aspas
                                                        }
                                                    }
                                                ?>
                                            </optgroup>
                                            <?php
                                                if ($result_setores_inativo && pg_num_rows($result_setores_inativo) > 0) {
                                                    echo "<optgroup label='Inativos'>";
                                                    while ($row_setor = pg_fetch_assoc($result_setores_inativo)) {
                                                        echo "<option value='$row_setor[nome_setor]'> $row_setor[nome_setor] </option>"; 
                                                        // cuidado com espaços depois e antes das aspas
                                                    }
                                                    echo "</optgroup>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="button-pesquisa">
                            <input type="submit" value="Buscar" id="botao-buscar">
                            <button type="button" id="avancada-button" class="avancada-button">Avançada</button>
                        </div> 
                    </form>
                </div>

                <!-- Listagem de resultados -->
                <div id="consulta-pacientes">
                    <div class="espace">
                        <table id="tabela-pacientes" class="display">
                            <tr class="cabecalho-tabela">
                                <th>Excluir</th>
                                <th>Nome</th>
                                <th>Nome da Mãe</th>
                                <th>CPF</th>
                                <th>Prontuário</th>
                                <th>Registro</th>
                                <th>Data Nasc.</th>
                                <th>Setor</th>
                                <th>Data cadastro</th>
                                <th>Editar</th>
                            </tr>

                            <!-- Dados dos pacientes -->
                            <?php
                                if (!$result_paciente) {
                                    echo "Erro ao gerar a query";
                                } else {
                                    $hasResults = false;

                                    if (pg_num_rows($result_paciente) > 0) {
                                        $hasResults = true;

                                        while ($row = pg_fetch_assoc($result_paciente)) {

                                            $dt_nascimento_formatado = date('d/m/Y', strtotime($row['dt_nasc']));
                                            $dt_requisicao_formatado = date('d/m/Y', strtotime($row['dt_requisicao']));

                                            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
                                            $get_pagina    = "";

                                            if(isset($_GET['pagina'])){
                                                $get_pagina = "&pagina=$_GET[pagina]";
                                            }

                                            echo "<tr>
                                                    <td onclick='pega_id($row[id_paciente])'><i class='fas fa-trash-alt' style='color: red;'></i></td>
                                                    <td style='text-align: left;' >" . (strlen($nome_paciente) > 30 ? substr($nome_paciente, 0, 30) . '...' : $nome_paciente) . "</td> 
                                                    <td style='text-align: left;' >" . (strlen($row['nome_mae']) > 30 ? substr($row['nome_mae'], 0, 30) . '...' : $row['nome_mae']) . "</td>
                                                    <td> $row[cpf] </td>
                                                    <td style='text-align: left;' > $row[prontuario] </td>
                                                    <td> $row[registro] </td>
                                                    <td> $dt_nascimento_formatado </td>
                                                    <td style='text-align: left;' >" . (strlen($row['nome_setor']) > 18 ? substr($row['nome_setor'], 0, 18) . '...' : $row['nome_setor']) . "</td>
                                                    <td> $dt_requisicao_formatado </td>
                                                    <td><a href='perfil_paciente.php?id_paciente=$row[id_paciente]$get_pagina'><i class='fas fa-pencil-alt'></i></a></td>
                                                </tr>";
                                        }
                                    }
                                    
                                    // Verifica casos que não há dados do filtro
                                    if (!$hasResults) {
                                        $message = 'Nenhum resultado encontrado para o filtro aplicado.';

                                        if (empty($result_paciente)) {
                                            if (!empty($nome_completo)) {
                                                $message = 'Nome não encontrado.';
                                            }elseif (!empty($nome_mae)) {
                                                $message = 'Nome da mãe não encontrado.';
                                            }elseif (!empty($prontuario)) {
                                                $message = 'Prontuário não encontrado.';
                                            }elseif (!empty($cpf)) {
                                                $message = 'CPF não cadastrado.';
                                            }elseif (!empty($nome_setor)) {
                                                $message = 'Nome do setor não encontrado.';
                                            }elseif (!empty($dt_nasc)) {
                                                $message = 'Data de nascimento não encontrada.';
                                            }elseif (!empty($dt_requisicao)) {
                                                $message = 'Data de requisição não encontrada.';
                                            }elseif (!empty($registro)) {
                                                $message = 'Número de registro não encontrado.';
                                            }
                                        }

                                        // Verifica se nenhum campo relevante foi preenchido
                                        if (empty($nome_completo) && empty($nome_mae) && empty($prontuario) && empty($cpf) &&
                                            empty($nome_setor) && empty($dt_nasc) && empty($dt_requisicao) && empty($registro)){
                                            $message = 'Preencha ao menos um campo para realizar a busca.';
                                        }

                                        echo "<tr><td colspan='10'>$message</td></tr>";
                                    }                                                                       
                                }
                            ?>
                        </table>
                    </div>
                </div>
                
                <?php
                    // Determina a página atual a partir da URL, ou define como 1 se não estiver definida
                    $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                ?>

                <div class="pagination-container">
                    <ul class="pagination">
                        <?php
                            if ($totalPaginas > 1) {
                                // Link para a página Anterior
                                if ($paginaAtual > 1) {
                                    echo '<li class="pagination-item">
                                            <a href="?pagina=' . ($paginaAtual - 1) . '">Anterior</a>
                                        </li>';
                                }

                                // Páginas numeradas e "..."
                                for ($i = 1; $i <= $totalPaginas; $i++) {
                                    if ($i === 1 || $i === $totalPaginas || ($i >= $paginaAtual - 2 && $i <= $paginaAtual + 2)) {
                                        $activeClass = ($i === $paginaAtual) ? 'active' : '';
                                        echo "<li class='pagination-item $activeClass'>
                                                <a href='?pagina=$i'> $i </a>
                                            </li>";
                                    } elseif ($i < $paginaAtual - 2 && $i > 1) {
                                        echo '<li class="pagination-item">
                                                <span>...</span>
                                            </li>';
                                        $i = $paginaAtual - 3;
                                    } elseif ($i > $paginaAtual + 2 && $i < $totalPaginas) {
                                        echo '<li class="pagination-item">
                                                <span>...</span>
                                            </li>';
                                        $i = $totalPaginas - 1;
                                    }
                                }

                                // Link para a página Seguinte
                                if ($paginaAtual < $totalPaginas) {
                                    echo '<li class="pagination-item">
                                            <a href="?pagina=' . ($paginaAtual + 1) . '">Seguinte</a>
                                        </li>';
                                }
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Botão flutuante para alerta de CPF não preenchido -->
    <div class="floating-alert" id="cpfAlertButton">
        <i class="fas fa-exclamation-circle" style="margin-top: 13px;"></i>
    </div>

    <!-- Modal para exibir pacientes com CPF não preenchido -->
    <div class="modal" id="cpfAlertModal" tabindex="-1" role="dialog" aria-labelledby="cpfAlertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px; width: 100%;" role="document">
            <div class="modal-content" style="max-height: 500px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="cpfAlertModalLabel">Pacientes com CPF não preenchido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body-alert" id="cpfAlertModalBody">
                    <div id="cpfTableDisplay">
                        <table class="display">
                            <thead>
                            <tr class="cabecalho-tabela">
                                    <th>Data de Cadastro</th>
                                    <th>Nome</th>
                                    <th>Nome da Mãe</th>
                                    <th>Prontuario</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Consulta SQL para selecionar registros sem CPF cadastrado (vazio)
                                    $query_cpf_vazio  = "SELECT * FROM sth_dados_paciente WHERE cpf = '000.000.000-00'";
                                    $result_cpf_vazio = conecta_query($conexao, $query_cpf_vazio);

                                    // Verifica se houve registros retornados pela consulta
                                    if (pg_num_rows($result_cpf_vazio) > 0) {
                                        // Exibe os pacientes com CPF não preenchido e armazena no array
                                        while ($row = pg_fetch_assoc($result_cpf_vazio)) {
                                            
                                            $nome_paciente           = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
                                            $dt_requisicao_formatado = date('d/m/Y', strtotime($row['dt_requisicao']));

                                            echo "<tr>
                                                    <td>$dt_requisicao_formatado</td>
                                                    <td style='text-align: left;'>$nome_paciente</td>
                                                    <td style='text-align: left;'>$row[nome_mae]</td>
                                                    <td>$row[prontuario]</td>
                                                    <td><a href='perfil_paciente.php?id_paciente=$row[id_paciente]'><i class='fas fa-pencil-alt'></i></a></td>
                                                </tr>";
                                        }
                                    } else {
                                        // Caso não haja pacientes com CPF não preenchido
                                        echo "<tr><td colspan='5'>Nenhum paciente encontrado com CPF não preenchido.</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>    
                </div>
            </div>
        </div>
    </div>

    <!-- Botão flutuante de ajuda -->
    <div class="floating-button" id="helpButton">
        <i class="fas fa-question" style="margin-top: 13px;"></i>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Como fazer a pesquisa?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>Na página de pesquisa, você encontrará campos para inserir critérios de pesquisa, como nome e cpf do paciente</li>
                        <li>O sistema oferece a opção "avançada", considere utilizá-las para refinar os resultados da pesquisa.</li>
                        <li>Após inserir os critérios de pesquisa, clique no botão "Buscar" para iniciar a pesquisa.</li>
                        <li>Os resultados geralmente são apresentados em forma de lista na tela.</li>
                        <li>Selecione o paciente desejado na lista de resultados para visualizar o registro completo.</li>
                    </ul>
                    <h6><strong>Como funciona os icones?</strong></h6>
                    <ul>
                        <li><i class='fas fa-pencil-alt' style='color: #005eff;'></i> - Acessar o Perfil do paciente</li>
                        <li><i class='fas fa-trash-alt' style='color: red;'></i> - Excluir/Unificar dados do paciente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal unificar dados - mostrar dados do antigo -->
    <div class="modal" id="paciente_antigo_unificar_dados" role="dialog" tabindex="-1" aria-labelledby="unificar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px; width: 100%;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="info_unificar">Informações do paciente a ser excluido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form action="unificar.php?unificar_resposta=2" method="POST" name="form_paciente_antigo_unificar_dados" id="form_paciente_antigo_unificar_dados" >
                        <?php
                            $qtd_paciente_antigo = 0;

                            $result_nome_paciente_antigo = conecta_query($conexao, "SELECT nome_social, nome_completo FROM sth_dados_paciente WHERE id_paciente = $id_paciente_antigo");
                            $row_nome_paciente_antigo    = pg_fetch_assoc($result_nome_paciente_antigo); 
                            $nome_paciente_antigo        = !empty($row_nome_paciente_antigo['nome_social']) ? $row_nome_paciente_antigo['nome_social'] : $row_nome_paciente_antigo['nome_completo'];
                        
                            $query_paciente_antigo = "SELECT cb.id_bolsa, cb.num_bolsa, cb.num_sus, h.sigla, c.id_controle, bd.id_bolsas_devolvidas 
                            FROM sth_cadastro_bolsa cb 
                            INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente 
                            LEFT JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente 
                            LEFT JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa 
                            LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa 
                            LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa 
                            WHERE dp.id_paciente = $id_paciente_antigo 
                            group by cb.id_bolsa, c.id_controle, bd.id_bolsas_devolvidas, h.sigla";

                            $result_paciente_antigo = conecta_query($conexao, $query_paciente_antigo);
                        ?>
                        <div class="row" >
                            <div class="col-lg-12" >
                                <p><strong>Registro a ser excluido: <br> Paciente: </strong><?php echo $nome_paciente_antigo;?></p>
                            </div>
                        </div>
                        <div class="row" >
                            <div class="col-lg-12" >
                                <p><strong>Bolsas vinculadas: </strong></p>
                                <div style="height:300px; overflow:auto; margin-top:20px;">
                                    <table class="table-striped" >
                                        <thead>
                                            <tr class="cabecalho-tabela">
                                                <th>Bolsa</th>
                                                <th>Hemoc</th>
                                                <th>SUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            while($row_paciente_antigo = pg_fetch_assoc($result_paciente_antigo)){
                                                $qtd_paciente_antigo++;

                                                echo "<tr> 
                                                        <td> $row_paciente_antigo[num_bolsa] </td> 
                                                        <td> $row_paciente_antigo[sigla] </td>
                                                        <td> $row_paciente_antigo[num_sus] </td>
                                                    </tr>";
                                            }

                                            $_SESSION["qtd_bolsa_paciente_antigo"] = $qtd_paciente_antigo;
                                        ?>
                                        </tbody>
                                    </table>   
                                </div> 
                            </div>
                        </div>
                        <div class="col-lg-12 botoes-unificar">
                            <button type="button" class="botao-verde" name="btn_dados_antigo" onclick="modal_selecionar()" >Unificar</button>
                            <button type="submit" class="botao-excluir">Excluir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal unificar - selecionar paciente -->
    <div class="modal" id="selecionado_paciente_unificar" role="dialog" tabindex="-1" aria-labelledby="unificar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 750px; width: 100%;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="info_unificar">Selecione o paciente que irá receber os dados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form action="unificar.php?unificar_resposta=0" method="POST" name="form_unificar" id="form_unificar" >
                        <div class="row">
                            <div class="col-lg-auto">
                                <label for="unificar" class="required"></label>
                                <select name="unificar" id="unificar" style="width:550px;" required>
                                    <option value="">Selecione</option>
                                    <?php
                                        while ($row_paciente_unificar = pg_fetch_assoc($result_paciente_unificar)) {
                                            echo "<option value='$row_paciente_unificar[id_paciente]'> $row_paciente_unificar[nome_completo] </option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-auto">
                                <button type="submit" class="botao-verde" name="btn_selecionado_unificar">Avançar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal unificar dados - escolher dados -->
    <div class="modal" id="paciente_unificar_dados" role="dialog" tabindex="-1" aria-labelledby="unificar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1100px; width: 100%;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="info_unificar">Informações da unificação de dados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form action="unificar.php?unificar_resposta=1" method="POST" name="form_unificar_dados" id="form_unificar_dados" >
                        <?php
                            $qtd_paciente_antigo = 0;

                            $result_nome_paciente_antigo = conecta_query($conexao, "SELECT nome_social, nome_completo FROM sth_dados_paciente WHERE id_paciente = $id_paciente_antigo");
                            $row_nome_paciente_antigo    = pg_fetch_assoc($result_nome_paciente_antigo); 
                            $nome_paciente_antigo        = !empty($row_nome_paciente_antigo['nome_social']) ? $row_nome_paciente_antigo['nome_social'] : $row_nome_paciente_antigo['nome_completo'];
                        
                            $query_paciente_antigo = "SELECT cb.id_bolsa, cb.num_bolsa, cb.num_sus, h.sigla, c.id_controle, bd.id_bolsas_devolvidas 
                            FROM sth_cadastro_bolsa cb 
                            INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente 
                            LEFT JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente 
                            LEFT JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa 
                            LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa 
                            LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa 
                            WHERE dp.id_paciente = $id_paciente_antigo 
                            group by cb.id_bolsa, c.id_controle, bd.id_bolsas_devolvidas, h.sigla";

                            $result_paciente_antigo = conecta_query($conexao, $query_paciente_antigo);
                            
                            // PACIENTE NOVO
                            $qtd_paciente_novo = 0;
                            $id_paciente_novo  = isset($_SESSION["unificar_id_paciente_novo"]) ? (int) $_SESSION["unificar_id_paciente_novo"] : 0;

                            $result_nome_paciente_novo = conecta_query($conexao, "SELECT nome_social, nome_completo FROM sth_dados_paciente WHERE id_paciente = $id_paciente_novo");
                            $row_nome_paciente_novo    = pg_fetch_assoc($result_nome_paciente_novo); 
                            $nome_paciente_novo        = !empty($row_nome_paciente_novo['nome_social']) ? $row_nome_paciente_novo['nome_social'] : $row_nome_paciente_novo['nome_completo'];
                        
                            $query_paciente_novo = "SELECT cb.id_bolsa, cb.num_bolsa, cb.num_sus, h.sigla, c.id_controle, bd.id_bolsas_devolvidas 
                            FROM sth_cadastro_bolsa cb 
                            INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente 
                            LEFT JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente 
                            LEFT JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa 
                            LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa 
                            LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa 
                            WHERE dp.id_paciente = $id_paciente_novo 
                            group by cb.id_bolsa, c.id_controle, bd.id_bolsas_devolvidas, h.sigla";

                            $result_paciente_novo = conecta_query($conexao, $query_paciente_novo);
                        ?>
                        <div class="row" >
                            <div class="col-lg-6" >
                                <div class="row" >
                                    <div class="col-lg-12" >
                                        <p><strong>Registro a ser excluido: <br> Paciente: </strong><?php echo $nome_paciente_antigo;?></p>
                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="col-lg-12" >
                                        <p><strong>Selecione quais bolsas unificar: </strong></p>
                                        <div style="height:300px; overflow:auto; margin-top:20px;">
                                            <table class="table-striped" >
                                                <thead>
                                                    <tr class="cabecalho-tabela">
                                                        <th>Bolsa</th>
                                                        <th>Hemoc</th>
                                                        <th>SUS</th>
                                                        <th>Unificar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        while($row_paciente_antigo = pg_fetch_assoc($result_paciente_antigo)){
                                                            $qtd_paciente_antigo++;
                                                            echo "<tr> 
                                                                    <td> $row_paciente_antigo[num_bolsa] </td> 
                                                                    <td> $row_paciente_antigo[sigla] </td>
                                                                    <td> $row_paciente_antigo[num_sus] </td>
                                                                    <td><input type='checkbox' name='bolsa_antigo_$qtd_paciente_antigo' id='bolsa_antigo_$qtd_paciente_antigo' value='$row_paciente_antigo[id_bolsa]'></td>
                                                                </tr>";
                                                        }
                                                        $_SESSION["qtd_bolsa_paciente_antigo"] = $qtd_paciente_antigo;
                                                    ?>
                                                </tbody>
                                            </table>   
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6" >
                                <div class="row" >
                                    <div class="col-lg-12" >
                                        <p><strong>Registro a receber os dados: <br> Paciente: </strong><?php echo $nome_paciente_novo;?></p>
                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="col-lg-12" >
                                        <p><strong>Bolsas vinculadas: </strong></p>
                                        <div style="height:300px; overflow:auto; margin-top:20px;">
                                            <table class="table-striped" >
                                                <thead>
                                                    <tr class="cabecalho-tabela">
                                                        <th>Bolsa</th>
                                                        <th>Hemoc</th>
                                                        <th>SUS</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        while($row_paciente_novo = pg_fetch_assoc($result_paciente_novo)){
                                                            $qtd_paciente_novo++;
                                                            echo "<tr> 
                                                                    <td> $row_paciente_novo[num_bolsa] </td> 
                                                                    <td> $row_paciente_novo[sigla] </td>
                                                                    <td> $row_paciente_novo[num_sus] </td>
                                                                </tr>";
                                                        }

                                                        if ($qtd_paciente_novo == 0){
                                                           echo "<tr> 
                                                                    <td colspan='4' > Nenhuma bolsa vinculada. </td> 
                                                                </tr>";
                                                        }
                                                    ?>
                                                </tbody>
                                            </table>   
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 botoes-unificar">
                            <button type="submit" class="botao-verde" name="btn_unificar">Unificar</button>
                            <button type="button" onclick="modal_selecionar()" class="botao-voltar">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal motivo exclusao -->
    <div class="modal" id="motivo_exclusao" role="dialog" tabindex="-1" aria-labelledby="unificar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; width: 100%;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title required" id="info_unificar">Digite o motivo da exclusão/unificação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form action="unificar.php" method="POST" name="form_unificar" id="form_unificar" >
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="unificar"></label>
                                <textarea name="motivo" id="motivo" cols="30" rows="10" required></textarea>
                                <input type="hidden" name="id_paciente" id="id_paciente">
                            </div>
                        </div>
                        <div class="row" >
                            <div class="col-lg-12 botoes-unificar">
                                <button type="submit" class="botao-verde" name="btn_motivo">Avançar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>

    <script type="text/javascript" src="js/script.js"></script>

    <script>
        $(document).ready(function(){
            // Ao clicar no botão de alerta de CPF
            $("#cpfAlertButton").click(function() {
                // Abre o modal
                $("#cpfAlertModal").modal("show");
            });
        });
        
        function modal_selecionar(){
            $("#selecionado_paciente_unificar").modal("show");
            $('#selecionado_paciente_unificar').css('z-index', '91050'); // valor alto aleatorio só para ficar na camada de cima
        };

        function pega_id(id_paciente){
            $('#motivo_exclusao').modal('show');
            document.getElementById("id_paciente").value = id_paciente
        }

        // BOTÃO DE CONFIRMAÇÃO DE EXCLUSÃO DE PACIENTE
        var form_excluir = document.getElementById('form_paciente_antigo_unificar_dados');
        form_excluir.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja excluir?",
                text: "Você irá excluir este paciente e todos os dados vinculados!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, excluir",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    form_excluir.submit();
                }
            });
        });

        // BOTÃO DE CONFIRMAÇÃO DE UNIFICAÇÃO DE PACIENTE
        var form_unifica = document.getElementById('form_unificar_dados');
        form_unifica.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja unificar?",
                text: "Você irá inserir os dados deste paciente em um outro paciente!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, unificar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    form_unifica.submit();
                }
            });
        });
        
        // Função para mostrar aba avançada
        toggleAdvancedSearch();
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    
</body>
</html>