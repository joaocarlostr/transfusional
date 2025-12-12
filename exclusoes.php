<?php
    include "database.php";
    include "function.php";

    // Query de busca, filtro e exibição
    // Helper para destacar termo
    function highlight_term($text, $term) {
        if (empty($term) || empty($text)) return htmlspecialchars($text);
        // Escapa caracteres especiais de regex no termo de pesquisa
        $regex = '/' . preg_quote($term, '/') . '/i';
        // Destaca a correspondência com Vermelho Escuro (#741c19) e fundo suave
        return preg_replace($regex, '<span style="color: #741c19; font-weight: 800; background-color: rgba(116, 28, 25, 0.1); padding: 0 2px; border-radius: 2px;">$0</span>', htmlspecialchars($text));
    }

    // Query de busca, filtro e exibição
    $exclusoes_por_pagina = 12;

    $tipo_registro  = isset($_GET['tipo_registro']) ? $_GET['tipo_registro'] : '';
    $dt_exclusao    = isset($_GET['dt_exclusoes'])  ? $_GET['dt_exclusoes']  : '';
    $termo_pesquisa = isset($_GET['termo_pesquisa']) ? $_GET['termo_pesquisa'] : '';

    // Filtros
    $where_clause = " WHERE 1=1";
    if (!empty($tipo_registro)) { 
        $where_clause .= " AND e.tipo_registro = '$tipo_registro'"; 
    }
    if (!empty($dt_exclusao)) { 
        $where_clause .= " AND to_char(e.dt_exclusao, 'YYYY-MM-DD') = '$dt_exclusao'"; 
    }
    if (!empty($termo_pesquisa)) {
        $term_safe = pg_escape_string($conexao, $termo_pesquisa);
        // Busca abrangente (Case Insensitive com ILIKE no Postgres)
        $where_clause .= " AND (
            e.prontuario ILIKE '%$term_safe%' OR 
            e.motivo ILIKE '%$term_safe%' OR 
            e.identificador::text ILIKE '%$term_safe%' OR 
            e.identificador_aux ILIKE '%$term_safe%' OR 
            p.nome_completo ILIKE '%$term_safe%'
        )";
    }

    //conta a qtd de exclusoes (com Join para o filtro funcionar no nome do paciente)
    $query_qtd_exclusoes  = "SELECT count(e.id_exclusoes) as qtd_exclusoes 
                             FROM sth_exclusoes e 
                             LEFT JOIN sth_dados_paciente p ON e.prontuario = p.prontuario 
                             $where_clause";

    $result_qtd_exclusoes = conecta_query($conexao, $query_qtd_exclusoes);
    $row_qtd_exclusoes    = pg_fetch_assoc($result_qtd_exclusoes);
    $total_exclusoes      = (int) $row_qtd_exclusoes["qtd_exclusoes"];

    $totalPaginas = ceil($total_exclusoes / $exclusoes_por_pagina);
    $paginaAtual  = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($paginaAtual < 1) $paginaAtual = 1;
    $offset       = ($paginaAtual - 1) * $exclusoes_por_pagina;

    //busca dados do exclusoes com LEFT JOIN para pegar nome do paciente
    // Alias tipo_registro para garantir que não seja sobrescrito pelo JOIN
    $query_exclusoes  = "SELECT e.*, e.tipo_registro as tipo_registro_real, p.nome_completo as nome_paciente_join 
                         FROM sth_exclusoes e 
                         LEFT JOIN sth_dados_paciente p ON e.prontuario = p.prontuario 
                         $where_clause 
                         ORDER BY e.dt_exclusao DESC LIMIT $exclusoes_por_pagina OFFSET $offset";
    
    $result_exclusoes = conecta_query($conexao, $query_exclusoes);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="css/style.css">
    <title>Histórico de Exclusões - HUM</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

    <style>
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        .container-crud { max-width: 1400px; margin: 90px auto 120px auto; padding: 0 20px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        .card-header-crud { background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); color: #fff !important; padding: 20px 30px; font-size: 1.3rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; } /* Menu Red Theme */
        .card-body-crud { padding: 30px; }
        
        .table-custom th { background-color: #6c757d !important; border-bottom: 2px solid #e9ecef; color: #fff !important; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; border-radius: 0; }
        .table-custom td { vertical-align: middle; color: #343a40; font-size: 0.8rem; }
        .table-custom tr:hover td { background-color: #f8f9fa; }
        .col-data { white-space: nowrap; width: 1%; } /* Prevent line break and auto-fit width */

        /* Updated Button Styles to fix overlap */
        .btn-cmd-base {
            flex: 1;
            height: 45px;
            border-radius: 30px;
            border: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none !important;
            white-space: nowrap; /* Prevent text wrapping */
        }

        .btn-cmd-search {
            background-color: #1976d2 !important; /* Blue Primary */
            color: white !important;
            margin-right: 10px; /* Space between buttons */
        }
        .btn-cmd-search:hover {
            background-color: #1565c0 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-cmd-clear {
            background-color: #ef6c00 !important; /* Orange Warning/Action */
            color: white !important;
        }
        .btn-cmd-clear:hover {
            background-color: #e65100 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .actions-wrapper {
            display: flex;
            width: 100%;
            align-items: flex-end;
            justify-content: space-between;
        }

        .form-label-custom { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 700; margin-left: 10px; margin-bottom: 5px; }

        .form-control { height: 45px; border-radius: 30px; padding-left: 20px; font-size: 0.9rem; }
        /* Fix for Select Arrow if missing */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        /* Restored Badge Styles */
        .badge-type { padding: 6px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; border: none; color: #fff; }
        .badge-bolsa { background-color: #d32f2f !important; color: #fff !important; } /* Red */
        .badge-reacao { background-color: #ef6c00 !important; color: #fff !important; } /* Orange */
        .badge-paciente { background-color: #1976d2 !important; color: #fff !important; } /* Blue */
        .badge-secondary { background-color: #546e7a !important; color: #fff !important; } /* Grey */
        
        /* Restored Floating Button */
        .floating-button { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background-color: #343a40; color: #fff; border-radius: 50%; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2); cursor: pointer; z-index: 1000; display: flex; align-items: center; justify-content: center; font-size: 24px; transition: transform 0.3s; }
        .floating-button:hover { transform: scale(1.1); background-color: #23272b; }
    </style>
</head>
<body>
    <?php include_once "includes/header.php"; ?>

    <div class="container container-crud">
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-history mr-2"></i> Histórico de Exclusões</span>
            </div>
            
            <!-- Filter Section -->
            <div class="p-4 border-bottom bg-light">
                <form id="formulario-pesquisa" method="GET" action="exclusoes.php">
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom" for="termo_pesquisa">Pesquisa Geral</label>
                            <input type="text" class="form-control" name="termo_pesquisa" id="termo_pesquisa" placeholder="Nome, Prontuário, Motivo..." value="<?php echo htmlspecialchars($termo_pesquisa); ?>">
                            <small class="form-text text-muted mt-1" style="font-size: 0.75rem; font-style: italic;">
                                <i class="fas fa-info-circle mr-1"></i> Busque por: Nome, Prontuário, Motivo, ID ou Dado Auxiliar.
                            </small>
                        </div>
                        <div class="col-md-3 mb-3">
                             <label class="form-label-custom" for="tipo_registro">Tipo de Registro</label>
                             <select name="tipo_registro" id="tipo_registro" class="form-control">
                                <option value="">Todos</option>
                                <option value="Bolsa" <?php if($tipo_registro == 'Bolsa') echo 'selected'; ?>>Bolsa</option>
                                <option value="Bolsa devolvida" <?php if($tipo_registro == 'Bolsa devolvida') echo 'selected'; ?>>Bolsa Devolvida</option>
                                <option value="Reacao" <?php if($tipo_registro == 'Reacao') echo 'selected'; ?>>Reação</option>
                                <option value="Paciente" <?php if($tipo_registro == 'Paciente') echo 'selected'; ?>>Paciente</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                             <label class="form-label-custom" for="dt_exclusoes">Data</label>
                            <input type="date" class="form-control" name="dt_exclusoes" id="dt_exclusoes" value="<?php echo htmlspecialchars($dt_exclusao); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom d-block">&nbsp;</label>
                            <div class="actions-wrapper">
                                <button type="submit" class="btn-cmd-base btn-cmd-search" title="Buscar">
                                    <i class="fas fa-search mr-2"></i> Buscar
                                </button>
                                <a href="exclusoes.php" class="btn-cmd-base btn-cmd-clear" title="Limpar Filtros">
                                    <i class="fas fa-eraser mr-2"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4 col-data">Data/Hora</th>
                                <th>Tipo</th>
                                <th class="text-left">Paciente</th>
                                <th class="text-left">Motivo</th>
                                <th>Prontuário</th>
                                <th>Bolsa</th>
                                <th>Auxiliar</th>
                                <th>Autor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_exclusoes && pg_num_rows($result_exclusoes) > 0): ?>
                                <?php while ($row = pg_fetch_assoc($result_exclusoes)): 
                                     if(empty($row["id_usuario"])){
                                        $nome_usuario_logado = "Sistema/Desconhecido";
                                    } else {
                                        $result_nome_usuario_logado = conecta_query($conexao, "SELECT de_usuario FROM usuario WHERE id = $row[id_usuario]");
                                        $row_nome_usuario_logado    = pg_fetch_assoc($result_nome_usuario_logado);
                                        $nome_usuario_logado        = $row_nome_usuario_logado["de_usuario"] ?? 'N/A';
                                    }
                                    
                                    // Pegar apenas o Primeiro Nome
                                    $partes_nome = explode(" ", trim($nome_usuario_logado));
                                    $primeiro_nome_usuario = $partes_nome[0];

                                    // Lógica de Badge usando alias explícito
                                    $tipo_show = $row['tipo_registro_real']; 
                                    $badgeClass = 'badge-secondary';
                                    if(stripos($tipo_show, 'bolsa') !== false) $badgeClass = 'badge-bolsa';
                                    elseif(stripos($tipo_show, 'reacao') !== false) $badgeClass = 'badge-reacao';
                                    elseif(stripos($tipo_show, 'paciente') !== false) $badgeClass = 'badge-paciente';

                                    // Determinar Nome do Paciente
                                    $nomePacienteDisplay = '';
                                    if ($tipo_show === 'Paciente') {
                                        // Para Paciente excluído, o nome está em identificador_aux
                                        $nomePacienteDisplay = $row['identificador_aux'];
                                    } else {
                                        // Tenta pegar do JOIN
                                        $nomePacienteDisplay = !empty($row['nome_paciente_join']) ? $row['nome_paciente_join'] : 'Paciente não encontrado';
                                    }

                                    // Destacar Correspondências
                                    $nome_exibicao = highlight_term($nomePacienteDisplay, $termo_pesquisa);
                                    $motivo_exibicao = highlight_term($row['motivo'], $termo_pesquisa);
                                    $prontuario_exibicao = highlight_term($row['prontuario'], $termo_pesquisa);
                                    $identificador_exibicao = highlight_term($row['identificador'], $termo_pesquisa);
                                    $aux_exibicao = highlight_term($row['identificador_aux'], $termo_pesquisa);
                                ?>
                                    <tr>
                                        <td class="pl-4 col-data"><?php echo date('d/m/Y H:i', strtotime($row['dt_exclusao'])); ?></td>
                                        <td><span class="badge badge-type <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($tipo_show); ?></span></td>
                                        <td class="text-left"><?php echo $nome_exibicao; ?></td>
                                        <td class="text-left" style="max-width: 300px;"><small class="text-muted"><?php echo $motivo_exibicao; ?></small></td>
                                        <td><?php echo $prontuario_exibicao; ?></td>
                                        <td><?php echo $identificador_exibicao; ?></td>
                                        <td><?php echo $aux_exibicao; ?></td>
                                        <td><i class="fas fa-user-circle mr-1 text-muted"></i> <?php echo htmlspecialchars($primeiro_nome_usuario); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">Nenhum registro de exclusão encontrado com os filtros atuais.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPaginas > 1): ?>
                <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                    <?php 
                        $start_record = ($paginaAtual - 1) * $exclusoes_por_pagina + 1;
                        $end_record = min($start_record + $exclusoes_por_pagina - 1, $total_exclusoes); 
                    ?>
                    <small class="text-primary" style="font-weight: 600;">Mostrando <?php echo $start_record; ?> - <?php echo $end_record; ?> de <?php echo $total_exclusoes; ?> registros</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <!-- First -->
                            <li class="page-item <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=1&termo_pesquisa=<?php echo urlencode($termo_pesquisa); ?>&tipo_registro=<?php echo urlencode($tipo_registro); ?>&dt_exclusoes=<?php echo urlencode($dt_exclusao); ?>">Primeira</a>
                            </li>

                            <!-- Previous -->
                            <li class="page-item <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>&termo_pesquisa=<?php echo urlencode($termo_pesquisa); ?>&tipo_registro=<?php echo urlencode($tipo_registro); ?>&dt_exclusoes=<?php echo urlencode($dt_exclusao); ?>">Anterior</a>
                            </li>

                            <!-- Simplified Pagination Logic similar to crud_paciente -->
                            <?php
                            $start_page = max(1, $paginaAtual - 2);
                            $end_page = min($totalPaginas, $paginaAtual + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                                $active = ($i == $paginaAtual) ? 'active' : '';
                            ?>
                                <li class="page-item <?php echo $active; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?>&termo_pesquisa=<?php echo urlencode($termo_pesquisa); ?>&tipo_registro=<?php echo urlencode($tipo_registro); ?>&dt_exclusoes=<?php echo urlencode($dt_exclusao); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>&termo_pesquisa=<?php echo urlencode($termo_pesquisa); ?>&tipo_registro=<?php echo urlencode($tipo_registro); ?>&dt_exclusoes=<?php echo urlencode($dt_exclusao); ?>">Próxima</a>
                            </li>

                            <!-- Last -->
                            <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $totalPaginas; ?>&termo_pesquisa=<?php echo urlencode($termo_pesquisa); ?>&tipo_registro=<?php echo urlencode($tipo_registro); ?>&dt_exclusoes=<?php echo urlencode($dt_exclusao); ?>">Última</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Floating Help Button -->
    <div class="floating-button" id="helpButton" data-toggle="modal" data-target="#helpModal">
        <i class="fas fa-question"></i>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold ml-2">Ajuda - Exclusões</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-muted">
                    <p>Esta tela apresenta o log de auditoria de todas as exclusões realizadas no sistema.</p>
                    <ul class="pl-3">
                        <li><strong>Identificador:</strong> O ID principal do registro excluído (ex: ID do Paciente).</li>
                        <li><strong>Dado Auxiliar:</strong> Informação extra para ajudar na identificação (ex: Nome do Paciente ou Sigla da Bolsa).</li>
                        <li><strong>Motivo:</strong> A justificativa fornecida pelo usuário no momento da exclusão.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>
    <script type="text/javascript" src="js/script.js"></script>
    <script>
        // Máscara de entrada simples para Prontuário se necessário, atualmente direto
    </script>
</body>
</html>