<?php
    //descomentar apenas no servidor principal
    // Verifica se o usuário está autenticado
    /*
    $id_usuario_logado = isset($_SESSION["id"]) ? (int) $_SESSION["id"] : 0;

    if ($id_usuario_logado === 0) {
        echo "<script>
                alert('Você não está devidamente autenticado! Faça seu login novamente.');
                window.location.href = 'http://186.233.152.78/shi/shi_menu_principal.php';
            </script>";
        exit;
    }
*/
    // Verifica se a sessão ainda não foi iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Inicia a sessão
    }

    $bolsa_reserva = 0;

    // BOLSAS RESERVA
    // Não dependemos mais de uma conexão armazenada em sessão (não serializável).
    // Se a variável $conexao não existir, tenta incluir o arquivo de configuração
    // do banco de dados para inicializar a conexão.
    if (!isset($conexao) || !$conexao) {
        $dbPath = __DIR__ . '/../database.php';
        if (file_exists($dbPath)) {
            include_once $dbPath;
        }
    }

    // Se a conexão estiver disponível, executa a consulta de bolsas reserva
    if (isset($conexao) && $conexao) {
        //seleciona todas as bolsas reservas 
        $query_bolsa_reserva = "SELECT cb.id_bolsa, dt_saida, num_bolsa, h.sigla, dp.prontuario 
        FROM sth_cadastro_bolsa cb
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
        INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
        WHERE cb.reserva = 'sim'
        ORDER BY dt_saida"; 

        $result_bolsa_reserva     = conecta_query($conexao, $query_bolsa_reserva);
        $result_bolsa_qtd_reserva = conecta_query($conexao, $query_bolsa_reserva);

        while (pg_fetch_assoc($result_bolsa_qtd_reserva)) { 
            $bolsa_reserva++;
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <title>Transfusional - HUM</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<header class="header">
    <nav class="navbar navbar-expand-lg">
        <!-- Logo -->
        <div class="logo">
            <img src="img/logo-HUM.png" alt="Logo da Empresa">
        </div>
        
        <?php if (defined('AMBIENTE_ALERTA')): ?>
            <div style="background-color: #4f54d9ff; color: white; padding: 2px 10px; font-weight: bold; font-size: 12px; border-radius: 4px; margin-left: 20px; white-space: nowrap;">
                <?php echo AMBIENTE_ALERTA; ?>
            </div>
        <?php endif; ?>

        <!-- Navbar colapse quando tela sm e md -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" id="teste" >
            <span class="navbar-toggler-icon"></span>
        </button>
    
        <!-- Navbar -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav teste">
                <li class="nav-item active">
                    <a class="" href="index.php">Inicio <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="" href="buscar_paciente.php">Buscar</a>
                </li>
                <!-- <li class="nav-item dropdown show">
                    <a href="#" class="dropdown-toggle" id="cadastroDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Buscar
                    </a>
                    <div class="dropdown-menu" aria-labelledby="cadastroDropdown">
                        <a class="dropdown-item" href="buscar_paciente.php">Paciente</a>
                        <a class="dropdown-item" href="buscar_bolsa.php">Bolsa</a>
                    </div>
                </li> -->
                <li class="nav-item dropdown show">
                    <a href="#" class="dropdown-toggle" id="cadastroDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Cadastro
                    </a>
                    <div class="dropdown-menu" aria-labelledby="cadastroDropdown">
                        <a class="dropdown-item" href="cadastro_paciente.php">Paciente</a>
                        <a class="dropdown-item" href="cadastrar_setor.php">Setor</a>
                        <a class="dropdown-item" href="cadastrar_responsavel.php">Responsável</a>
                        <a class="dropdown-item" href="cadastrar_nao_conformidade.php">Não conformidade</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="" href="relatorio.php">Relatórios</a>
                </li>
                <li class="nav-item">
                    <a class="" href="info_gerais.php">Informações</a>
                </li>
                <li class="nav-item">
                    <a class="" href="exclusoes.php">Exclusões</a>
                </li>
                <li class="nav-item">
                    <a href="#" title="Bolsas reserva" data-toggle="modal" data-target="#notificaçãoVencido">
                        <i class="fa fa-bell sino"></i>
                        <button class="notificacao"><?php echo $bolsa_reserva; ?></button>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="http://186.233.152.78/shi/shi_menu_principal.php" title="Sair">
                        <i class="fa fa-sign-out" aria-hidden="true" style="margin-top:5px; font-size:25px;"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<body>
    <!-- Modal de Notificação bolsas reservas -->
    <div class="modal" id="notificaçãoVencido" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 700px; width: 100%;" role="document">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Bolsas reserva</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div style="height:300px; overflow:scroll;">
                        <table class="table-striped" >
                            <tr class="cabecalho-tabela">
                                <!-- <th>ID</th> -->
                                <th>Data saida</th>
                                <th>Num bolsa</th>
                                <th>Hemoc.</th>
                                <th>Prontuario</th>
                            </tr>
                            <?php
                                if (isset($conexao) && $conexao) {
                                    if (!$result_bolsa_reserva) {
                                        echo "<tr>
                                                <td colspan='5'> Não foi possível gerar a query </td>
                                            </tr>";
                                    } else {
                                        if (pg_num_rows($result_bolsa_reserva) > 0) {
                                            while ($row_bolsa_reserva = pg_fetch_assoc($result_bolsa_reserva)) { 

                                                $dt_saida = !empty($row_bolsa_reserva['dt_saida'])
                                                ? date('d/m/Y', strtotime($row_bolsa_reserva['dt_saida']))
                                                : "Não saiu";

                                                echo "<tr>
                                                        <td>$dt_saida                      </td>
                                                        <td>$row_bolsa_reserva[num_bolsa]  </td>
                                                        <td>$row_bolsa_reserva[sigla]      </td>
                                                        <td>$row_bolsa_reserva[prontuario] </td>
                                                    </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5'>Nenhuma bolsa reserva no momento.</td></tr>";
                                        }                                                                  
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>Nenhuma bolsa reserva no momento.</td></tr>";
                                }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>