<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para setores
    $query_setores         = "SELECT * FROM sth_setores ORDER BY nome_setor";
    $result_setores        = conecta_query($conexao, $query_setores);
    $result_setores_editar = conecta_query($conexao, $query_setores);

    $_SESSION['insere']   = "inserir_setor";
    $_SESSION['atualiza'] = "atualiza_setor";
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

    <title>Cadastro - Setor</title>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <!-- Corpo -->
    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Cadastro de setor</strong></p>
                <!-- Formulário -->
                <div id="formulario" class="container-setor" >
                    <form action="insere.php" method="POST" name="form" id="form_setor" >
                        <?php
                            if(isset($_SESSION['validado_setor']) && $_SESSION['validado_setor'] == 0){
                                exibir_mensagem_simples("Adicionado!", "Setor adicionado com sucesso.", "success");
                            }

                            $_SESSION['validado_setor'] = -1;

                            if(isset($_SESSION['validado_setor_editar']) && $_SESSION['validado_setor_editar'] == 0){
                                exibir_mensagem_simples("Editado!", "Setor editado com sucesso.", "success");
                            }

                            $_SESSION['validado_setor_editar'] = -1;
                        ?>
                        
                        <div class="row" >
                            <div class="col-sm-12 col-md-12 col-lg-7">
                                <label for="setor" class="required">Nome do setor:</label><br>
                                <input type="text" name="setor" id="setor" size="50" maxlength="200" autocomplete="off" required>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-5">
                                <label for="status">Status: (Selecione se o setor estiver ativo)</label><br>
                                <input type='checkbox' name='status' id='status' value='ativo'>
                            </div>
                        </div>

                        <br>
                        <div class="botoes-setor">
                            <input type="submit" class="btn botao-verde"  value="Adicionar"  name="inserir_reacao">
                            <input type="reset"  class="btn botao-limpar" value="Limpar Campos">
                            <button type="button" class="btn botao-editar" data-bs-target='#setores_modal' data-bs-toggle='modal'>
                                <i class='fas fa-pencil-alt'></i> Editar
                            </button>
                        </div>
                    </form>

                    <div style="height:350px; overflow:auto; margin-top:20px;">
                        <table class="table-striped" >
                            <tr class="cabecalho-tabela">
                                <th>Nome do Setor</th>
                                <th>Status</th>  
                            </tr>
                        
                            <!-- Dados dos pacientes -->
                            <?php
                                if (!$result_setores) {
                                    echo "Erro ao gerar a query";
                                }else{
                                    if(pg_num_rows($result_setores) > 0) {
                                        while ($row_setores = pg_fetch_assoc($result_setores)) {
                                            $status = !empty($row_setores['status']) ? "checked" : null;
                                
                                            echo "<tr>
                                                    <td style='text-align: left;'> $row_setores[nome_setor] </td> 
                                                    <td><input type='checkbox' name='status_setor' $status onclick='return false;'></td>
                                                </tr>";
                                        }
                                    }else{
                                        echo "<tr>
                                                <td colspan='2'>Nenhum registo encontrado</td> 
                                            </tr>";
                                     }                                                                  
                                }
                            ?>
                        </table>
                    </div>

                    <!-- Modal edição de informações -->
                    <div class="modal" id="setores_modal" role="dialog" tabindex="-1" aria-labelledby="info" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px; width: 100%;" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="info">Editar informações</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="atualiza.php" method="POST" name="form_editar" id="form_setor_editar" >
                                        <div class="row">
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <label for="setores" class="required">Selecione o setor para editar:</label><br>
                                                <select name="setores" id="setores" onchange='editar()' required>
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        while ($row_setores_editar = pg_fetch_assoc($result_setores_editar)) {
                                                            echo "<option value='$row_setores_editar[id_setor]' data-status='$row_setores_editar[status]'>
                                                                $row_setores_editar[nome_setor]
                                                            </option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" >
                                            <div class="col-sm-12 col-md-12 col-lg-12" >
                                                <label for="setor_editar" class="required" >Novo nome do setor:</label><br>
                                                <input type="text" name="setor_editar" id="setor_editar" maxlength="200" required>
                                            </div>
                                        </div>
                                        <div class="row" >
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <label for="status_editar">Status:</label>
                                                <input type='checkbox' name='status_editar' id="status_editar" value='ativo'>
                                            </div>
                                        </div>
                                        <div class="row botoes-setor" >
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <button type="submit" class="btn botao-verde" name="atualizar_setor">Editar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Setor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;">Como adicionar um setor?</p>
                    <ul>
                        <li>Insira o nome do setor.</li>
                        <li>Selecione a caixa do campo Status se o setor estiver ativo.</li>
                        <li>Clique no botão Adicionar e confirme a adição.</li>
                    </ul>
                    <p style="font-weight: bold;">Como editar um setor?</p>
                    <ul>
                        <li>Clique no botão editar que possui um lápis.</li>
                        <li>Selecione o setor que deseja editar.</li>
                        <li>Altere o nome do setor se desejar.</li>
                        <li>Selecione a caixa do campo Status se o setor estiver ativo ou a desmarque se desejar inativar o setor.</li>
                        <li>Clique no botão Editar.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Função que busca dados escolhidos para editar
        function editar() {
            var selectSetor   = document.getElementById('setores');
            var selectedSetor = selectSetor.options[selectSetor.selectedIndex];
            var status        = selectedSetor.dataset.status;

            document.getElementById('setor_editar').value  = selectedSetor.text;

            if(selectedSetor.dataset.status == "ativo"){
                document.getElementById('status_editar').setAttribute('checked',true);
            }else{
                document.getElementById('status_editar').removeAttribute("checked");
            }
        }

        // BOTÃO DE CONFIRMAÇÃO DE INSERÇÃO DE REAÇÃO TRANSFUSIONAL
        const form = document.getElementById('form_setor');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja adicionar?",
                text: "Você irá adicionar um novo setor!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, adicionar",
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>

</body>
</html>