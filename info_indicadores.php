<?php
include "database.php";
include "function.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.1/css/all.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <title>Sistema Transfusional - HUM</title>

    <style>
        .pdf-link .far.fa-file-pdf {
            color: red;
        }
    </style>
</head>

<body>
    <?php include_once "includes/header.php"; ?>

    <div class="container-index">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Indicadores</strong></p>
                <div id="texto" class="text-index">
                    <h5><strong>Arquivos Indicadores</strong></h5>
                    <p>Os indicadores são ferramentas essenciais para avaliar e monitorar 
                        a qualidade dos serviços de hemoterapia e garantir a segurança dos pacientes. Esses indicadores oferecem uma visão 
                        quantitativa e qualitativa das práticas transfusionais, auxiliando na identificação de áreas de melhoria e no alcance 
                        de padrões de excelência. </p>
                </div>
                <!-- Adicione este HTML à sua página onde deseja exibir a lista de PDFs -->
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="pdf-list">
                                <ul id="fileList">
                                    <li><a href="#" class="pdf-link"><i class="fas fa-folder"></i>Transfusões Realizadas</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4 mx-auto">
                            <div class="pdf-list">
                                <ul id="fileList">
                                    <li><a href="#" class="pdf-link"><i class="fas fa-folder"></i> Reações Transfusionais</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4 mx-auto">
                            <div class="pdf-list">
                                <ul id="fileList">
                                    <?php
                                    // Terceira coluna 
                                    // for ($ano = 2024; $ano <= ; $ano++) {
                                    //     echo "<li><a href=\"#\" class=\"pdf-link\" onclick=\"criarPasta('$ano')\"><i class=\"fas fa-folder\"></i> $ano</a></li>";
                                    // }
                                    ?>
                                </ul>
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
                                <h5 class="modal-title" id="helpModalLabel">Ajuda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Conteúdo da primeira ajuda -->
                                <p>Nessa página você tem acesso aos arquivos instrucionais, sobre a tranfusão de sangue.</p>

                                <!-- Conteúdo da terceira ajuda -->
                                <ul>
                                    <li>Item 1</li>
                                    <li>Item 2</li>
                                    <li>Item 3</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once "includes/footer.php"; ?>

    <script>
        // Função para exibir os arquivos selecionados
        function displaySelectedFiles() {
            // Seleciona o input de arquivo
            var fileInput = document.getElementById('fileInput');

            // Seleciona a lista onde os arquivos serão exibidos
            var fileList = document.getElementById('fileList');

            // Limpa a lista antes de adicionar os novos arquivos
            fileList.innerHTML = '';

            // Verifica se pelo menos um arquivo foi selecionado
            if (fileInput.files.length === 0) {
                return;
            }

            // Itera sobre os arquivos selecionados
            for (var i = 0; i < fileInput.files.length; i++) {
                // Cria um novo elemento de lista
                var listItem = document.createElement('li');

                // Cria um link para o arquivo PDF
                var link = document.createElement('a');
                link.href = URL.createObjectURL(fileInput.files[i]);
                link.textContent = fileInput.files[i].name;
                listItem.appendChild(link);

                // Adiciona um botão de exclusão
                var deleteButton = document.createElement('button');
                deleteButton.textContent = 'Excluir';
                deleteButton.className = 'btn btn-danger btn-sm mx-1';
                deleteButton.onclick = function() {
                    fileList.removeChild(listItem);
                    saveFilesToLocalStorage();
                    displaySelectedFiles();
                };
                listItem.appendChild(deleteButton);

                // Adiciona um botão de alteração
                var changeButton = document.createElement('button');
                changeButton.textContent = 'Alterar';
                changeButton.className = 'btn btn-primary btn-sm mx-1';
                changeButton.onclick = function() {
                    fileInput.click(); // Abre o seletor de arquivo
                };
                listItem.appendChild(changeButton);

                // Adiciona o item da lista à lista
                fileList.appendChild(listItem);
            }

            // Salva os arquivos selecionados no localStorage
            saveFilesToLocalStorage();
        }

        // Função para salvar os arquivos selecionados no localStorage
        function saveFilesToLocalStorage() {
            var files = [];
            var fileList = document.getElementById('fileList');
            var listItems = fileList.getElementsByTagName('li');
            for (var i = 0; i < listItems.length; i++) {
                files.push(listItems[i].getElementsByTagName('a')[0].textContent);
            }
            localStorage.setItem('selectedFiles', JSON.stringify(files));
        }

        // Função para carregar os arquivos selecionados do localStorage
        function loadFilesFromLocalStorage() {
            var files = JSON.parse(localStorage.getItem('selectedFiles'));
            if (files) {
                var fileList = document.getElementById('fileList');
                for (var i = 0; i < files.length; i++) {
                    var listItem = document.createElement('li');
                    var link = document.createElement('a');
                    link.href = '#'; // O link não será funcional após recarregar a página
                    link.textContent = files[i];
                    listItem.appendChild(link);

                    // Adiciona um botão de exclusão
                    var deleteButton = document.createElement('button');
                    deleteButton.textContent = 'Excluir';
                    deleteButton.className = 'btn btn-danger btn-sm mx-1';
                    deleteButton.onclick = function() {
                        fileList.removeChild(listItem);
                        saveFilesToLocalStorage();
                        displaySelectedFiles();
                    };
                    listItem.appendChild(deleteButton);

                    // Adiciona um botão de alteração
                    var changeButton = document.createElement('button');
                    changeButton.textContent = 'Alterar';
                    changeButton.className = 'btn btn-primary btn-sm mx-1';
                    changeButton.onclick = function() {
                        fileInput.click(); // Abre o seletor de arquivo
                    };
                    listItem.appendChild(changeButton);

                    // Adiciona o item da lista à lista
                    fileList.appendChild(listItem);
                }
            }
        }

        // Adiciona um ouvinte de evento para o evento change no input de arquivo
        document.getElementById('fileInput').addEventListener('change', displaySelectedFiles);

        // Carrega os arquivos selecionados do localStorage quando a página é carregada
        window.addEventListener('load', loadFilesFromLocalStorage);
    </script>

    <script type="text/javascript" src="js/script.js"></script>
</body>

</html>