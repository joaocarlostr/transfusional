<?php
    include "database.php";
    include "function.php";

    // Query de busca, filtro e exibição
    $exclusoes_por_pagina = 12;

    // Inicializa as variáveis
    $tipo_registro = $dt_exclusao = $prontuario = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $tipo_registro = isset($_POST['tipo_registro']) ? $_POST['tipo_registro'] : '';
        $dt_exclusao   = isset($_POST['dt_exclusoes'])  ? $_POST['dt_exclusoes']  : '';
        $prontuario    = isset($_POST['prontuario'])    ? $_POST['prontuario']    : '';
    }

    //conta a qtd de exclusoes
    $query_qtd_exclusoes  = "SELECT count(id_exclusoes) as qtd_exclusoes FROM sth_exclusoes";
    $result_qtd_exclusoes = conecta_query($conexao, $query_qtd_exclusoes);
    $row_qtd_exclusoes    = pg_fetch_assoc($result_qtd_exclusoes);
    $total_exclusoes      = (int) $row_qtd_exclusoes["qtd_exclusoes"];

    $totalPaginas = ceil($total_exclusoes / $exclusoes_por_pagina);
    $paginaAtual  = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
    $offset       = ($paginaAtual - 1) * $exclusoes_por_pagina;

    //busca dados do exclusoes
    $query_exclusoes  = "SELECT * FROM sth_exclusoes WHERE 1=1";

    //dados do exclusoes
    if (!empty($tipo_registro)) {
        $query_exclusoes .= " AND tipo_registro LIKE '%$tipo_registro%'";
    }
    if (!empty($dt_exclusao)) {
        $query_exclusoes .= " AND to_char(dt_exclusao, 'YYYY-MM-DD') = '$dt_exclusao'";
    }
    if (!empty($prontuario)) {
        $query_exclusoes .= " AND prontuario LIKE '%$prontuario%'";
    }

    //exclusoes que aparecem na busca
    $query_exclusoes .= " ORDER BY dt_exclusao LIMIT $exclusoes_por_pagina OFFSET $offset";
    $result_exclusoes = conecta_query($conexao, $query_exclusoes);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">

    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    
    <title>Exclusões - HUM</title>
</head> 

<body>
    <?php include_once "includes/header.php"; ?>
    <div class="container-exclusoes" id="container-exclusoes">
        <div class="fundo-imagem">
            <div id="corpo-pacientes" class="borda-pacientes">
                <p class="mensagem-borda"><strong>Buscar exclusões</strong></p>
                <!-- Formulário de busca -->
                <div class="pesquisa">
                    <form id="formulario-pesquisa" method="POST" action="exclusoes.php">
                        <p class="legenda"><strong>Pesquisar Exclusões:</strong></p>
                        
                        <div class="row" >
                            <div class="col-sm-12 col-md-4 col-lg-2" >
                                <label for="prontuario">Prontuario:</label><br>
                                <input type="text" size="20" name="prontuario" id="prontuario" oninput="apenasNumeros(this)" maxlength="15" >
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-3" >
                                <label for="tipo_registro">Tipo de registro:</label><br>
                                <select name="tipo_registro" id="tipo_registro">
                                    <option value="">Selecione</option>
                                    <option value="Bolsa">Bolsa</option>
                                    <option value="Bolsa devolvida">Bolsa devolvida</option>
                                    <option value="Reacao">Reação</option>
                                    <option value="Paciente">Paciente</option>
                                </select>
                            </div> 
                            <div class="col-sm-12 col-md-4 col-lg-2">
                                <label for="dt_exclusoes">Data Exclusão:</label><br>
                                <input type="date" name="dt_exclusoes" id="dt_exclusoes">
                            </div>
                        </div>

                        <div class="button-pesquisa">
                            <input type="submit" value="Buscar" id="botao-buscar">
                        </div> 
                    </form>
                </div>

                <!-- Listagem de resultados -->
                <div id="consulta-exclusoes">
                    <div class="espace">
                        <table id="tabela-exclusoes" class="display">
                            <tr class="cabecalho-tabela">
                                <th>Data exclusão</th>
                                <th>Tipo de registro</th>
                                <th>Motivo</th>
                                <th>Prontuario</th>  
                                <th>Identificador</th>
                                <th>Identificador auxiliar</th>
                                <th>Usuario</th>
                            </tr>

                            <!-- Dados dos exclusoess -->
                            <?php
                                if (!$result_exclusoes) {
                                    echo "Erro ao gerar a query";
                                } else {
                                    $hasResults = false;
                                    if (pg_num_rows($result_exclusoes) > 0) {
                                        $hasResults = true;

                                        while ($row = pg_fetch_assoc($result_exclusoes)) {

                                            if(empty($row["id_usuario"])){
                                                $nome_usuario_logado = "Usuario nao identificado";
                                            }else{
                                                $result_nome_usuario_logado = conecta_query($conexao, "SELECT de_usuario FROM usuario WHERE id = $row[id_usuario]");
                                                $row_nome_usuario_logado    = pg_fetch_assoc($result_nome_usuario_logado);
                                                $nome_usuario_logado        = $row_nome_usuario_logado["de_usuario"];
                                            }
                                            
                                            $get_pagina = "";

                                            if(isset($_GET['pagina'])){
                                                $get_pagina = "&pagina=$_GET[pagina]";
                                            }

                                            $data = date('d/m/Y H:i:s', strtotime($row['dt_exclusao']));

                                            echo "<tr>
                                                    <td>$data</td>
                                                    <td style='text-align: left;'> $row[tipo_registro] </td>
                                                    <td style='text-align: left;'> $row[motivo] </td>
                                                    <td style='text-align: left;'> $row[prontuario] </td> 
                                                    <td style='text-align: left;'> $row[identificador] </td> 
                                                    <td style='text-align: left;'> $row[identificador_aux] </td> 
                                                    <td style='text-align: left;'> $nome_usuario_logado </td> 
                                                </tr>";
                                        }
                                    }  
                                    
                                    // Verifica casos que não há dados do filtro
                                    if (!$hasResults) {
                                        $message = 'Nenhum resultado encontrado para o filtro aplicado.';
                                    
                                        if (!empty($tipo_registro) && empty($result_exclusoes)) {
                                            $message = 'Nome não encontrado.';
                                        } elseif (!empty($dt_exclusao) && empty($result_exclusoes)) {
                                            $message = 'Nome da mãe não encontrado.';
                                        } elseif (!empty($prontuario) && empty($result_exclusoes)) {
                                            $message = 'Prontuario não encontrado.';
                                        } elseif (empty($tipo_registro) && empty($dt_exclusao) && empty($prontuario) && empty($dt_nasc) && empty($dt_exclusao)) {
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

                                        echo "<li class='pagination-item $activeClass '>
                                                <a href='?pagina=$i'>$i</a>
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

    <!-- Botão flutuante de ajuda -->
    <div class="floating-button" id="helpButton">
        <i class="fas fa-question" style="margin-top: 13px;"></i>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>Na página de exclusões você verá quais dados foram apagados.</li>
                        <li>O sistema oferece a opção de data e hora da exclusão assim como o usuario que estava logado quando foi realizada a ação, dentre outros.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>

    <script type="text/javascript" src="js/script.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

</body>
</html>