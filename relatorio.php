<?php
    include "database.php";
    include "function.php";

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function obterOpcoesDoBanco($conexao, $tabela, $nomeColuna, $valorColuna, $nomeColunaAdicional = ''){
        $opcoes  = "<option value=\"\">Selecione</option>";
        $query   = "SELECT $nomeColuna, $valorColuna";
        
        if (!empty($nomeColunaAdicional)) {
            $query .= ", $nomeColunaAdicional";
        }
    
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
    
    $opcoesSetoresAtivos   = obterOpcoesDoBanco($conexao, 'sth_Setores', 'nome_setor', 'id_setor', 'status');
    $opcoesSetoresInativos = obterOpcoesDoBanco($conexao, 'sth_setores', 'nome_setor', 'id_setor', 'status');
    // $opcoesBolsa e $opcoesProntuario removidos para usar AJAX
    $opcoesHemocomponentes = obterOpcoesDoBanco($conexao, 'sth_Hemocomponentes', 'sigla', 'id_hemocomponente', 'descricao');

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

    $opcoesTipoReacaoImediata = obterOpcoesTipoReacao($conexao, "Reações Imediatas");
    $opcoesTipoReacaoTardia   = obterOpcoesTipoReacao($conexao, "Reações Tardias");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/flatpickr-custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <title>Gerador de Relatórios - HUM</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <style>
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        .container-crud { max-width: 1400px; margin: 90px auto 120px auto; padding: 0 20px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        .card-header-crud { background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); color: #fff !important; padding: 20px 30px; font-size: 1.3rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; } /* Red Theme */
        .card-body-crud { padding: 40px; }
        
        .btn-generate { 
            background-color: #28a745 !important; 
            color: #fff !important; 
            border: none !important; 
            font-weight: 600 !important; 
            padding: 10px 25px !important; 
            font-size: 0.95rem !important; 
            border-radius: 30px !important; 
            text-decoration: none !important; 
            cursor: pointer !important; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important; 
            transition: all 0.3s !important; 
            display: inline-block !important; 
            opacity: 1 !important; 
            visibility: visible !important; 
            position: relative !important; 
            top: auto !important; 
            left: auto !important; 
        }
        .btn-generate:hover { background-color: #218838 !important; transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0,0,0,0.15) !important; }
        
        .btn-clear { 
            background-color: #6c757d !important; 
            color: #fff !important; 
            border: none !important; 
            font-weight: 600 !important; 
            padding: 10px 25px !important; 
            border-radius: 30px !important; 
            text-decoration: none !important; 
            cursor: pointer !important; 
            transition: all 0.3s !important; 
            display: inline-block !important; 
            opacity: 1 !important; 
            visibility: visible !important; 
            position: relative !important; 
            top: auto !important; 
            left: auto !important; 
        }
        .btn-clear:hover { background-color: #5a6268 !important; transform: translateY(-2px); }

        .form-control:focus, .select2-container--default .select2-selection--single:focus { border-color: #a02c28; box-shadow: 0 0 0 3px rgba(160, 44, 40, 0.1); }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; color: #495057; margin-bottom: 8px; }
        
        /* Custom Standard Select Arrow */
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

        /* Select2 Style Overrides with Custom Arrow */
        .select2-container .select2-selection--single { 
            height: 45px !important; 
            border: 1px solid #ced4da; 
            border-radius: 0.25rem;
            /* Match standard select arrow style */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { 
            line-height: 43px; 
            padding-left: 15px;
            padding-right: 2.5rem; /* Space for arrow */
        }
        /* Hide default Select2 arrow */
        .select2-container--default .select2-selection--single .select2-selection__arrow { 
            display: none !important; 
        }
        
        /* FIX: Select2 dropdown z-index - deve aparecer acima de tudo */
        .select2-container--open {
            z-index: 99999 !important;
        }
        .select2-dropdown {
            z-index: 99999 !important;
        }
        .select2-container {
            z-index: 9999 !important;
        }
        
        /* Garantir que o container não corte o dropdown */
        .card-crud, .card-body-crud, .container-crud {
            overflow: visible !important;
        }
        
        /* Garantir que rows não cortem */
        .row {
            overflow: visible !important;
        }
        

        .form-control { height: 45px; }

        .section-title { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 25px; color: #741c19; font-weight: 700; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 10px; }

        /* Autocomplete Results Styling - Enhanced */
        .autocomplete-results {
            position: absolute;
            z-index: 99999;
            background: white;
            border: 1px solid #ced4da;
            border-top: none;
            max-height: 350px;
            overflow-y: auto;
            width: calc(100% - 30px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            border-radius: 0 0 8px 8px;
            display: none;
            animation: slideDown 0.2s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .autocomplete-results.show {
            display: block;
        }
        
        /* Custom scrollbar for autocomplete */
        .autocomplete-results::-webkit-scrollbar {
            width: 8px;
        }
        
        .autocomplete-results::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 0 8px 0;
        }
        
        .autocomplete-results::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .autocomplete-results::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .autocomplete-item {
            padding: 14px 18px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            color: #333;
        }
        
        .autocomplete-item:hover, .autocomplete-item.active {
            background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 3px solid #741c19;
            padding-left: 15px;
        }
        
        .autocomplete-item:last-child {
            border-bottom: none;
            border-radius: 0 0 8px 8px;
        }
        
        .autocomplete-loading {
            padding: 16px 18px;
            text-align: center;
            color: #741c19;
            font-style: italic;
            font-weight: 500;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .autocomplete-no-results {
            padding: 16px 18px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        
        .autocomplete-field {
            transition: all 0.3s ease;
        }
        
        .autocomplete-field:focus {
            border-color: #741c19;
            box-shadow: 0 0 0 3px rgba(116, 28, 25, 0.1);
            transform: translateY(-1px);
        }

        /* Modern Date Input Styling */
        input[type="date"] {
            position: relative;
            padding: 12px 45px 12px 15px !important;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            color: #333;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            transition: all 0.3s ease;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            text-align: left !important;
        }
        
        input[type="date"]:hover {
            border-color: #741c19;
            background: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(116, 28, 25, 0.1);
        }
        
        input[type="date"]:focus {
            outline: none;
            border-color: #741c19;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(116, 28, 25, 0.1);
            transform: translateY(-1px);
        }
        
        /* Calendar icon styling */
        input[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute;
            right: 12px;
            cursor: pointer;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23741c19' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E") no-repeat center;
            width: 24px;
            height: 24px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 1;
            transform: scale(1.1);
        }
        
        /* Date icon on the left - Desabilitado para Flatpickr */
        .date-input-wrapper {
            position: relative;
        }
        
        /* Removido - Flatpickr cria seu próprio input
        .date-input-wrapper::before {
            content: "📅";
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            pointer-events: none;
            z-index: 1;
            opacity: 0.8;
        }
        */

        
        /* Placeholder styling for empty dates */
        input[type="date"]:invalid {
            color: #999;
        }
        
        input[type="date"]:valid {
            color: #333;
            font-weight: 600;
        }



        /* FIX: Sobrescrever CSS global que esconde botões */
        button.btn-generate, button.btn-clear {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            transform: none !important;
        }

        .floating-button { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background-color: #343a40; color: #fff; border-radius: 50%; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2); cursor: pointer; z-index: 1000; display: flex; align-items: center; justify-content: center; font-size: 24px; transition: transform 0.3s; }
        .floating-button:hover { transform: scale(1.1); background-color: #23272b; }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <div class="container container-crud">
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-chart-line mr-2"></i> Gerador de Relatórios</span>
            </div>
            <div class="card-body card-body-crud">
                
                <form class="row g-3" id="form_relatorio" action="gerar_relatorio.php" method="POST" target="_blank" onsubmit="return validarFormulario()" enctype="multipart/form-data">
                     <?php
                        if(isset($_SESSION['validado_relatorio']) && $_SESSION['validado_relatorio'] == 0){
                            echo "<script>Swal.fire('Atenção', 'Campos associados incorretamente.', 'warning');</script>";
                        }
                        $_SESSION['validado_relatorio'] = 1;

                        if(isset($_SESSION['validado_relatorio_vazio']) && $_SESSION['validado_relatorio_vazio'] == 0){
                            echo "<script>Swal.fire('Vazio', 'Não há registros para esta requisição.', 'info');</script>";
                        }
                        $_SESSION['validado_relatorio_vazio'] = 1;
                        
                        // Handle other session errors similarly if needed
                    ?>

                    <div class="section-title"><i class="fas fa-filter mr-2"></i> Filtros Principais</div>
                    
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="data_inicio">Data de Início</label>
                            <div class="date-input-wrapper">
                                <input type="date" name="data_inicio" id="data_inicio" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="data_fim">Data de Fim</label>
                            <div class="date-input-wrapper">
                                <input type="date" name="data_fim" id="data_fim" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="tipo">Tipo de Relatório</label>
                             <select name="tipo" id="tipo" class="form-control" style="width: 100%;">
                                <option value="">Selecione o tipo de relatório...</option>
                                <optgroup label="Bolsas">
                                    <option value="bolsa">Bolsas transfundidas</option>
                                    <option value="bolsa_devolvida">Bolsas não transfundidas</option>
                                    <option value="bolsa_reserva">Bolsas reserva</option>
                                    <option value="bolsa_repetida">Bolsas repetidas</option>
                                </optgroup>
                                <optgroup label="Pacientes">
                                    <option value="paciente">Pacientes transfundidos</option>
                                    <option value="paciente_sem_registro">Pacientes sem registro</option>
                                    <option value="tipo_reacao_paciente">Reações por paciente</option>
                                </optgroup>
                                <optgroup label="Indicadores">
                                    <option value="indi_nao_conformidade">Indicador de não conformidade</option>
                                    <option value="reacao_transfusional">Indicador de reação transfusional</option>
                                    <option value="indi_bolsa_reserva">Indicador de bolsas reserva</option>
                                    <option value="indi_bolsa_devolvida">Indicador de bolsas não transfundidas</option>
                                </optgroup>
                                <optgroup label="Outros">
                                    <option value="nao_conformidade">Não conformidade</option>
                                    <option value="tipo_sanguineo">Tipo sanguíneo</option>
                                    <option value="tipo_setor">Setores</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-4"><i class="fas fa-list-alt mr-2"></i> Filtros Específicos</div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="prontuario_search">Prontuário (Pesquisa)</label>
                            <input type="text" 
                                   id="prontuario_search" 
                                   class="form-control autocomplete-field" 
                                   placeholder="Digite Nome ou Prontuário..."
                                   autocomplete="off">
                            <input type="hidden" name="prontuario" id="prontuario">
                            <div id="prontuario_results" class="autocomplete-results"></div>
                        </div>
                        <div class="col-md-6 form-group">
                             <label for="bolsa_search">Bolsa Específica (Pesquisa)</label>
                             <input type="text" 
                                    id="bolsa_search" 
                                    class="form-control autocomplete-field" 
                                    placeholder="Digite o Número da Bolsa..."
                                    autocomplete="off">
                             <input type="hidden" name="bolsa" id="bolsa">
                             <div id="bolsa_results" class="autocomplete-results"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="hemocomponente">Hemocomponente</label>
                            <select name="hemocomponente" id="hemocomponente" class="form-control" style="width: 100%;">
                                <option value="">Todos</option>
                                <option value="todos">Todos (Explícito)</option>
                                <?php echo $opcoesHemocomponentes; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                             <label for="tipo_reacao">Tipo de Reação</label>
                             <select name="tipo_reacao" id="tipo_reacao" class="form-control" style="width: 100%;">
                                <option value="">Todas</option>
                                <option value="todas">Todos (Explícito)</option>
                                <optgroup label="Imediatas">
                                    <?php echo $opcoesTipoReacaoImediata; ?>
                                </optgroup>
                                <optgroup label="Tardias">
                                    <?php echo $opcoesTipoReacaoTardia; ?>
                                </optgroup>
                             </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label for="id_setor">Setor</label>
                            <select name="id_setor" id="id_setor" class="form-control" style="width: 100%;">
                                <option value="">Todos</option>
                                <optgroup label="Ativos">
                                    <?php echo $opcoesSetoresAtivos; ?>
                                    <option value="pa_geral">PA - GERAL</option>
                                </optgroup>
                                <optgroup label="Inativos">
                                    <?php echo $opcoesSetoresInativos; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-7 form-group">
                            <label for="importa_arquivo">Comparação com Hemocentro (CSV)</label>
                            <div class="custom-file">
                                <input type="hidden" name="MAX_SIZE_FILE" value="10000000">
                                <input type="file" class="custom-file-input" name="importa_arquivo" id="importa_arquivo" accept=".csv">
                                <label class="custom-file-label" for="importa_arquivo">Escolher arquivo...</label>
                            </div>
                            <small class="form-text text-muted">Use apenas arquivos .csv para comparação de bolsas.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="reset" class="btn btn-clear mr-3" onclick="limpa_select2()"><i class="fas fa-eraser mr-2"></i> Limpar Campos</button>
                        <button type="submit" class="btn btn-generate" name="gerar_relatorio"><i class="fas fa-file-pdf mr-2"></i> Gerar Relatório</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Floating Help Button -->
    <div class="floating-button" id="helpButton" data-toggle="modal" data-target="#helpModal">
        <i class="fas fa-question"></i>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-primary font-weight-bold"><i class="fas fa-info-circle mr-2"></i> Ajuda com Relatórios</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                     <div class="alert alert-info">
                        <strong>Dica:</strong> Para gerar um relatório, preencha os filtros desejados e clique em "Gerar Relatório". A maioria dos relatórios requer pelo menos data de início e fim.
                    </div>
                    <!-- Reuse the help content but organized better -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-uppercase text-secondary font-weight-bold border-bottom pb-2">Bolsas</h6>
                            <ul class="text-muted small">
                                <li><strong>Transfundidas:</strong> Use Data Início/Fim. Filtros opcionais: Setor, Bolsa, Prontuário.</li>
                                <li><strong>Não Transfundidas:</strong> Use Data Início/Fim e/ou Bolsa.</li>
                                <li><strong>Comparação Hemocentro:</strong> Converta a planilha XLSX para CSV e use o campo de upload. Selecione as datas.</li>
                            </ul>
                            
                            <h6 class="text-uppercase text-secondary font-weight-bold border-bottom pb-2 mt-3">Indicadores</h6>
                            <ul class="text-muted small">
                                <li>Selecione o indicador desejado e o período (Data Início/Fim).</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript" src="js/flatpickr-init.js"></script>
    <script>
        $(document).ready(function() {
            // Select2 comentado - usando autocomplete customizado para Prontuário e Bolsa
            // $(".select2").select2({
            //     language: "pt-BR",
            //     placeholder: "Selecione uma opção",
            //     allowClear: true
            // });


            // Custom Autocomplete for Prontuario with Keyboard Navigation
            let prontuarioTimeout;
            let prontuarioActiveIndex = -1;
            
            $('#prontuario_search').on('input', function() {
                const query = $(this).val().trim();
                const resultsDiv = $('#prontuario_results');
                prontuarioActiveIndex = -1;
                
                clearTimeout(prontuarioTimeout);
                
                if (query.length < 1) {
                    resultsDiv.removeClass('show').empty();
                    $('#prontuario').val('');
                    return;
                }
                
                resultsDiv.html('<div class="autocomplete-loading">🔍 Buscando...</div>').addClass('show');
                
                prontuarioTimeout = setTimeout(function() {
                    $.ajax({
                        url: 'ajax_search.php?type=prontuario',
                        method: 'GET',
                        data: { q: query },
                        dataType: 'json',
                        success: function(data) {
                            resultsDiv.empty();
                            
                            if (data.results && data.results.length > 0) {
                                data.results.forEach(function(item, index) {
                                    const div = $('<div class="autocomplete-item"></div>')
                                        .text(item.text)
                                        .data('id', item.id)
                                        .data('index', index)
                                        .on('click', function() {
                                            $('#prontuario_search').val(item.text);
                                            $('#prontuario').val(item.id);
                                            resultsDiv.removeClass('show').empty();
                                        });
                                    resultsDiv.append(div);
                                });
                                resultsDiv.addClass('show');
                            } else {
                                resultsDiv.html('<div class="autocomplete-no-results">Nenhum resultado encontrado</div>').addClass('show');
                            }
                        },
                        error: function(xhr, status, error) {
                            resultsDiv.html('<div class="autocomplete-no-results">Erro ao buscar dados</div>').addClass('show');
                        }
                    });
                }, 300);
            });

            // Keyboard navigation for Prontuario
            $('#prontuario_search').on('keydown', function(e) {
                const resultsDiv = $('#prontuario_results');
                const items = resultsDiv.find('.autocomplete-item');
                
                if (!items.length) return;
                
                // Arrow Down
                if (e.keyCode === 40) {
                    e.preventDefault();
                    prontuarioActiveIndex = (prontuarioActiveIndex + 1) % items.length;
                    items.removeClass('active');
                    $(items[prontuarioActiveIndex]).addClass('active').get(0).scrollIntoView({ block: 'nearest' });
                }
                // Arrow Up
                else if (e.keyCode === 38) {
                    e.preventDefault();
                    prontuarioActiveIndex = prontuarioActiveIndex <= 0 ? items.length - 1 : prontuarioActiveIndex - 1;
                    items.removeClass('active');
                    $(items[prontuarioActiveIndex]).addClass('active').get(0).scrollIntoView({ block: 'nearest' });
                }
                // Enter
                else if (e.keyCode === 13 && prontuarioActiveIndex >= 0) {
                    e.preventDefault();
                    $(items[prontuarioActiveIndex]).click();
                }
                // Escape
                else if (e.keyCode === 27) {
                    resultsDiv.removeClass('show').empty();
                }
            });


            // Custom Autocomplete for Bolsa with Keyboard Navigation
            let bolsaTimeout;
            let bolsaActiveIndex = -1;
            
            $('#bolsa_search').on('input', function() {
                const query = $(this).val().trim();
                const resultsDiv = $('#bolsa_results');
                bolsaActiveIndex = -1;
                
                clearTimeout(bolsaTimeout);
                
                if (query.length < 1) {
                    resultsDiv.removeClass('show').empty();
                    $('#bolsa').val('');
                    return;
                }
                
                resultsDiv.html('<div class="autocomplete-loading">🔍 Buscando...</div>').addClass('show');
                
                bolsaTimeout = setTimeout(function() {
                    $.ajax({
                        url: 'ajax_search.php?type=bolsa',
                        method: 'GET',
                        data: { q: query },
                        dataType: 'json',
                        success: function(data) {
                            resultsDiv.empty();
                            
                            if (data.results && data.results.length > 0) {
                                data.results.forEach(function(item, index) {
                                    const div = $('<div class="autocomplete-item"></div>')
                                        .text(item.text)
                                        .data('id', item.id)
                                        .data('index', index)
                                        .on('click', function() {
                                            $('#bolsa_search').val(item.text);
                                            $('#bolsa').val(item.id);
                                            resultsDiv.removeClass('show').empty();
                                        });
                                    resultsDiv.append(div);
                                });
                                resultsDiv.addClass('show');
                            } else {
                                resultsDiv.html('<div class="autocomplete-no-results">Nenhuma bolsa encontrada</div>').addClass('show');
                            }
                        },
                        error: function() {
                            resultsDiv.html('<div class="autocomplete-no-results">Erro ao buscar dados</div>').addClass('show');
                        }
                    });
                }, 300);
            });

            // Keyboard navigation for Bolsa
            $('#bolsa_search').on('keydown', function(e) {
                const resultsDiv = $('#bolsa_results');
                const items = resultsDiv.find('.autocomplete-item');
                
                if (!items.length) return;
                
                // Arrow Down
                if (e.keyCode === 40) {
                    e.preventDefault();
                    bolsaActiveIndex = (bolsaActiveIndex + 1) % items.length;
                    items.removeClass('active');
                    $(items[bolsaActiveIndex]).addClass('active').get(0).scrollIntoView({ block: 'nearest' });
                }
                // Arrow Up
                else if (e.keyCode === 38) {
                    e.preventDefault();
                    bolsaActiveIndex = bolsaActiveIndex <= 0 ? items.length - 1 : bolsaActiveIndex - 1;
                    items.removeClass('active');
                    $(items[bolsaActiveIndex]).addClass('active').get(0).scrollIntoView({ block: 'nearest' });
                }
                // Enter
                else if (e.keyCode === 13 && bolsaActiveIndex >= 0) {
                    e.preventDefault();
                    $(items[bolsaActiveIndex]).click();
                }
                // Escape
                else if (e.keyCode === 27) {
                    resultsDiv.removeClass('show').empty();
                }
            });


            // Close autocomplete when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.form-group').length) {
                    $('.autocomplete-results').removeClass('show').empty();
                }
            });


            // Update file input label
            $('.custom-file-input').on('change', function() { 
               let fileName = $(this).val().split('\\').pop(); 
               $(this).next('.custom-file-label').addClass("selected").html(fileName); 
            });
        });

        function limpa_select2() {
            $('.select2').val(null).trigger('change');
            $('.custom-file-label').html('Escolher arquivo...');
        }

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
                Swal.fire('Atenção', 'Por favor, selecione pelo menos um filtro ou tipo de relatório.', 'warning');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>