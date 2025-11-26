<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para nao_conformidades
    $query_nao_conformidade       = "SELECT * FROM sth_nao_conformidade ORDER BY tipo, nao_conformidade";
    $result_nao_conformidade      = conecta_query($conexao, $query_nao_conformidade);
    $result_nao_conformidade_view = conecta_query($conexao, $query_nao_conformidade);

    $_SESSION['insere']   = "inserir_nao_conformidade";
    $_SESSION['atualiza'] = "atualizar_nao_conformidade";
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

    <title>Cadastro - Não conformidade</title>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <!-- Corpo -->
    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Cadastro de não conformidade</strong></p>
                <!-- Formulário -->
                <div id="formulario" class="container-nao-conformidade" >
                    <form action="insere.php" method="POST" name="form" id="form_nao_conformidade" >
                        <?php
                            if(isset($_SESSION['validado_nao_conformidade']) && $_SESSION['validado_nao_conformidade'] == 0){
                                exibir_mensagem_simples("Adicionado!", "Não conformidade adicionada com sucesso.", "success");
                            }

                            $_SESSION['validado_nao_conformidade'] = -1;

                            if(isset($_SESSION['validado_nao_conformidade_editar']) && $_SESSION['validado_nao_conformidade_editar'] == 0){
                                exibir_mensagem_simples("Editado!", "Não conformidade editada com sucesso.", "success");
                            }
                            
                            $_SESSION['validado_nao_conformidade_editar'] = -1;
                        ?>

                        <div class="row" >
                            <div class="col-lg-5">
                                <label for="tipo" class="required">Tipo</label><br>
                                <select name="tipo" id="tipo" required>
                                    <option value="">Selecione</option>
                                    <option value="Prescrição médica">Prescrição médica</option>
                                    <option value="Ficha de controle de sinais vitais">Ficha de controle de sinais vitais</option>
                                    <option value="Livro de registro de hemocomponentes">Livro de registro de hemocomponentes</option>
                                    <option value="Formulário de devolução de hemocomponentes">Formulário de devolução de hemocomponentes</option>
                                    <option value="Outros">Outros</option>
                                </select>
                            </div>
                            <div class="col-lg-7">
                                <label for="nao_conformidade" class="required">Não conformidade:</label><br>
                                <input type="text" name="nao_conformidade" id="nao_conformidade" size="50" maxlength="200" required>
                            </div>
                        </div>

                        <br>
                        <div class="botoes-nao-conformidade">
                            <input type="submit" class="btn botao-verde"  value="Adicionar" name="inserir_reacao" onclick="removeRequired()" >
                            <input type="reset" class="btn botao-limpar" value="Limpar Campos">
                            <button type="button" class="btn botao-editar" data-bs-target='#nao_conformidade_modal' data-bs-toggle='modal'>
                                <i class='fas fa-pencil-alt'></i> Editar
                            </button>
                        </div>
                    </form>

                    <div style="height:350px; overflow:auto; margin-top:20px;">
                        <table class="table-striped" >
                            <tr class="cabecalho-tabela">
                                <th>Tipo</th>
                                <th>Não conformidade</th>
                                <th>Status</th>
                            </tr>
                        
                            <!-- Dados dos pacientes -->
                            <?php
                                if (!$result_nao_conformidade) {
                                    echo "Erro ao gerar a query";
                                }else{
                                    if(pg_num_rows($result_nao_conformidade) > 0) {
                                        while ($row_nao_conformidade = pg_fetch_assoc($result_nao_conformidade)) {

                                            $status = !empty($row_nao_conformidade['status']) ? "checked" : null;
                                    
                                            echo "<tr>
                                                    <td> $row_nao_conformidade[tipo] </td>
                                                    <td style='text-align: left;'> $row_nao_conformidade[nao_conformidade] </td>
                                                    <td style='text-align: left;'><input type='checkbox' name='status_setor' $status onclick='return false;'></td>
                                                </tr>";
                                                // <td><a href=''><i class='fas fa-trash-alt' style='color: red;'></i></a></td>
                                        }
                                    }else{
                                        echo "<tr>
                                                <td colspan='3'>Nenhum registo encontrado</td> 
                                            </tr>";
                                     }                                                                  
                                }
                            ?>
                        </table>
                    </div>

                    <div class="modal" id="nao_conformidade_modal" role="dialog" tabindex="-1" aria-labelledby="info" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px; width: 100%;" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="info">Editar informações</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="atualiza.php" method="POST" name="form_editar" id="form_nao_conformidade_editar" >
                                        <div class="row">
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <label for="nao_conformidades" class="required">Selecione a não conformidade:</label>
                                                <select name="nao_conformidades" id="nao_conformidades" onchange='editar()'  required>
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        while ($row_nao_conformidade_view = pg_fetch_assoc($result_nao_conformidade_view)) {
                                                            echo "<option value ='$row_nao_conformidade_view[id_nao_conformidade]' 
                                                                    data-tipo   = '$row_nao_conformidade_view[tipo]' 
                                                                    data-status = '$row_nao_conformidade_view[status]'>
                                                                    $row_nao_conformidade_view[nao_conformidade]
                                                                </option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" >
                                            <div class="col-sm-12 col-md-12 col-lg-12" >
                                                <label for="nao_conformidade_editar" class="required" >Nova não conformidade:</label><br>
                                                <input type="text" name="nao_conformidade_editar" id="nao_conformidade_editar" maxlength="200" autocomplete="off" required>
                                            </div>
                                        </div>
                                        <div class="row" >
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <label for="tipo_editar" class="required">Tipo</label><br>
                                                <select name="tipo_editar" id="tipo_editar" required>
                                                    <option value="">Selecione</option>
                                                    <option value="Prescrição médica">Prescrição médica</option>
                                                    <option value="Ficha de controle de sinais vitais">Ficha de controle de sinais vitais</option>
                                                    <option value="Livro de registro de hemocomponentes">Livro de registro de hemocomponentes</option>
                                                    <option value="Formulário de devolução de hemocomponentes">Formulário de devolução de hemocomponentes</option>
                                                    <option value="Outros">Outros</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" >
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <label for="status_editar">Status (Selecione se a não conformidade estiver ativa): </label>
                                                <input type='checkbox' name='status' id='status_editar' value='ativo'>
                                            </div>
                                        </div>
                                        <div class="row botoes-nao-conformidade" >
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <button type="submit" class="btn botao-verde" name="atualizar_nao_conformidade">Editar</button>
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Responsável</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;">Como adicionar um Não conformidade?</p>
                    <ul>
                        <li>Insira o nome do Não conformidade.</li>
                        <li>Selecione a caixa do campo Status se o Não conformidade estiver ativo.</li>
                        <li>Clique no botão Adicionar e confirme a adição.</li>
                    </ul>
                    <p style="font-weight: bold;">Como editar um Não conformidade?</p>
                    <ul>
                        <li>Clique no botão editar que possui um lápis.</li>
                        <li>Selecione o Não conformidade que deseja editar.</li>
                        <li>Altere o nome do Não conformidade se desejar.</li>
                        <li>Selecione a caixa do campo Status se o Não conformidade estiver ativo ou a desmarque se desejar inativar o Não conformidade.</li>
                        <li>Clique no botão Editar.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Traz dados escolhidos para editar
        function editar() {
            var selectNao_conformidade   = document.getElementById('nao_conformidades');
            var selectedNao_conformidade = selectNao_conformidade.options[selectNao_conformidade.selectedIndex];

            document.getElementById('nao_conformidade_editar').value = selectedNao_conformidade.text;
            document.getElementById('tipo_editar').value             = selectedNao_conformidade.dataset.tipo;

            if(selectedNao_conformidade.dataset.status == "ativo"){
                document.getElementById('status_editar').setAttribute('checked',true);
            }else{
                document.getElementById('status_editar').removeAttribute("checked");
            }
        }

        // BOTÃO DE CONFIRMAÇÃO DE INSERÇÃO DE REAÇÃO TRANSFUSIONAL
        const form = document.getElementById('form_nao_conformidade');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja adicionar?",
                text: "Você irá adicionar uma nova não conformidade!",
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