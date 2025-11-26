<?php
    include "database.php";
    include "function.php";

    // Query de busca, filtro e exibição
    $bolsas_por_pagina = 12;

    // Inicializa as variáveis
    $numero_bolsa = $sus_bolsa = $data_transfusao = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $numero_bolsa    = isset($_POST['numero_bolsa'])    ? $_POST['numero_bolsa']    : '';
        $sus_bolsa       = isset($_POST['sus_bolsa'])       ? $_POST['sus_bolsa']       : '';
        $data_transfusao = isset($_POST['data_transfusao']) ? $_POST['data_transfusao'] : '';
    }

    $query_qtd_bolsas  = "SELECT count(id_bolsa) as qtd_bolsa FROM sth_cadastro_bolsa";
    $result_qtd_bolsas = conecta_query($conexao, $query_qtd_bolsas);
    $row_qtd_bolsas    = pg_fetch_assoc($result_qtd_bolsas);
    $total_bolsas      = (int) $row_qtd_bolsas["qtd_bolsa"];

    $totalPaginas = ceil($total_bolsas / $bolsas_por_pagina);
    $paginaAtual  = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
    $offset       = ($paginaAtual - 1) * $bolsas_por_pagina;

    $query_bolsa  = "SELECT cb.*, dp.prontuario, nome_social, nome_completo, h.sigla, cb.horario_inicio,
    CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome 
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
    WHERE 1=1";

    if (!empty($numero_bolsa)) {
        $query_bolsa .= " AND num_bolsa LIKE '%$numero_bolsa%'";
    }
    if (!empty($data_transfusao)) {
        $query_bolsa .= " AND to_char(data_transfusao, 'YYYY-MM-DD') = '$data_transfusao'";
    }
    if (!empty($sus_bolsa)) {
        $query_bolsa .= " AND num_sus LIKE '%$sus_bolsa%'";
    }

    $query_bolsa .= " ORDER BY data_transfusao LIMIT $bolsas_por_pagina OFFSET $offset";
    $result_bolsa = conecta_query($conexao, $query_bolsa);
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
    
    <title>Buscar bolsas - HUM</title>
</head> 

<body>
    <?php include_once "includes/header.php"; ?>
    <div class="container-exclusoes" id="container-exclusoes">
        <div class="fundo-imagem">
            <div id="corpo-pacientes" class="borda-pacientes">
                <p class="mensagem-borda"><strong>Buscar bolsas</strong></p>
                <!-- Formulário de busca -->
                <div class="pesquisa">
                    <form id="formulario-pesquisa" method="POST" action="buscar_bolsa.php">
                        <p class="legenda"><strong>Pesquisar Bolsas:</strong></p>
                        
                        <div class="row" >
                            <div class="col-sm-12 col-md-4 col-lg-2" >
                                <label for="numero_bolsa">Número da bolsa:</label><br>
                                <input type="text" size="20" name="numero_bolsa" id="numero_bolsa" oninput="formatarCodigo(this)" minlength="13" maxlength="13" >
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-3" >
                                <label for="sus_bolsa">SUS da bolsa:</label><br>
                                <input type="text" size="20" name="sus_bolsa" id="sus_bolsa" oninput="validarSUSBolsa(this)" maxlength="11" >
                            </div> 
                            <div class="col-sm-12 col-md-4 col-lg-2">
                                <label for="data_transfusao">Data transfusão:</label><br>
                                <input type="date" name="data_transfusao" id="data_transfusao">
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
                                <th style='text-align: left;'>Data transfusão</th>
                                <th style='text-align: left; width:100px;'>Horário início</th>
                                <th style='text-align: left; width:100px;'>Número bolsa</th>
                                <th style='text-align: left;'>Hemocomponente</th>
                                <th style='text-align: left; width:100px;'>SUS da bolsa</th>
                                <th style='text-align: left;'>Prontuario</th>  
                                <th style='text-align: left; width:500px;'>Nome paciente</th>  
                            </tr>

                            <?php
                                if (!$result_bolsa) {
                                    echo "Erro ao gerar a query";
                                } else {
                                    $hasResults = false;
                                    if (pg_num_rows($result_bolsa) > 0) {
                                        $hasResults = true;

                                        while ($row = pg_fetch_assoc($result_bolsa)) {

                                            $data          = date('d/m/Y', strtotime($row['data_transfusao']));
                                            $hora_minuto   = SubStr($row['horario_inicio'], 0, 5);
                                            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];

                                            echo "<tr>
                                                    <td style='text-align: left;'> $data </td> 
                                                    <td style='text-align: left;'> $hora_minuto </td> 
                                                    <td style='text-align: left;'> $row[num_bolsa] </td>
                                                    <td style='text-align: left;'> $row[sigla] </td>
                                                    <td style='text-align: left;'> $row[num_sus] </td>
                                                    <td style='text-align: left;'> $row[prontuario] </td>
                                                    <td style='text-align: left;' >" . (strlen($nome_paciente) > 30 ? substr($nome_paciente, 0, 30) . '...' : $nome_paciente) . "</td> 
                                                </tr>";
                                        }
                                    }  
                                    
                                    // Verifica casos que não há dados do filtro
                                    if (!$hasResults) {
                                        $message = 'Nenhum resultado encontrado para o filtro aplicado.';
                                    
                                        if (!empty($numero_bolsa) && empty($result_bolsa)) {
                                            $message = 'Nome não encontrado.';
                                        } elseif (!empty($data_transfusao) && empty($result_bolsa)) {
                                            $message = 'Nome da mãe não encontrado.';
                                        } elseif (!empty($sus_bolsa) && empty($result_bolsa)) {
                                            $message = 'Prontuario não encontrado.';
                                        } elseif (empty($numero_bolsa) && empty($data_transfusao) && empty($sus_bolsa)) {
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