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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>

    <title>Informações - HUM</title>
</head>

<body>
    <?php include_once "includes/header.php"; ?>

    <div class="container-index"> <!-- mudar para container-info-gerais -->
        <div class="fundo-imagem">
            <div id="corpo-index" class="borda-info-gerais">
                <p class="mensagem-borda"><strong>Informações</strong></p>
                <div id="" class="text-info-gerais">
                    <p>
                        A Diretoria de Enfermagem (DEE) do HUM organizou o Serviço Transfusional em 2021, visando criar
                        uma ligação entre as atividades no hospital e no Hemocentro. Com o objetivo de monitorar as
                        transfusões sanguíneas, esclarecer dúvidas, oferecer capacitação e contribuir para a elaboração
                        de protocolos, o serviço também alimenta o Sistema de Controle Hemoterápico da Vigilância
                        Sanitária da Secretaria de Saúde do Paraná. O Comitê Transfusional, em parceria com o Serviço
                        Transfusional, utiliza estratégias para destacar a importância do uso adequado de documentos
                        como Requisições Transfusionais, investigar eventos adversos, lidar com não conformidades na
                        administração de hemocomponentes e monitorar reações adversas. Essas ações têm como objetivo
                        aprimorar a qualidade da assistência prestada e garantir a Segurança Transfusional no ambiente
                        hospitalar.
                    </p>
                </div>

                <!-- Adicione este HTML à sua página onde deseja exibir a lista de PDFs -->
                <div class="container-info">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 mx-auto">
                            <!-- Primeira coluna -->
                            <div class="pdf-list">
                                <p>Arquivos Instrucionais</p>
                                <ul>
                                    <li>
                                        <a href="arquivos/consentimento-info-transfusao.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Consentimento Informado Transfusão
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/formulario-controle-temperatura-geladeira.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Formulário Controle Temperatura Geladeira
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/POP-controle-temperatura-geladeira.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> POP Controle Temperatura Geladeira
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/reserva-cirurgica.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Reserva Cirúrgica
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/sangria-terapeutica.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Sangria Terapêutica
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/transfusao-macica.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Transfusão Maciça
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/transfusao-sangue.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Transfusão de Sangue
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/DHC - formulário de devolução de hemocomponentes.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Formulário Devolução de Hemocomponentes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/RT requisição de transfusão.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i> Requisição de Transfusão
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/CSV-REV-06-controle-sinais-vitais.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Controle de Sinais Vitais
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Segunda coluna -->
                        <div class="col-sm-12 col-md-6 col-lg-6 mx-auto">
                            <div class="pdf-list">
                                <p>Arquivos Adicionais</p>
                                <ul>
                                    <li>
                                        <a href="arquivos/CICLO DO SANGUE - ATO TRANSFUSIONAL.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Ciclo de Sangue - Ato Transfusional
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/manual_tecnico_hemovigilancia_08112007.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Manual Técnico de Hemovigilância - 2007
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/manual_hemovigilancia_2022.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Manual Técnico de Hemovigilancia - 2022
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/Portaria_Consolidacao_5_28_SETEMBRO_2017.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Portaria de Consolidação Nº 5
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/guia_uso_hemocomponentes_2ed.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Guia para o uso de Hemocomponentes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/PORTARIA Nº 158, DE 4 DE FEVEREIRO DE 2016.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Portaria Nº 158 - 2016
                                        </a>
                                    </li>
                                    <li>
                                        <a href="arquivos/DOCUMENTO DE ORIENTAÇÕES PARA O SISTEMA.pdf" class="pdf-link" target="_blank">
                                            <i class="far fa-file-pdf pdf-icon"></i>Documentação Orientação para o Sistema Transfusional
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.google.com/intl/pt-br/drive/about.html" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">
                                            <i class="fab fa-google-drive"></i> / Google Drive
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://lookerstudio.google.com/navigation/reporting" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">
                                            <i class="fab fa-google"></i> / Comparativos
                                        </a>
                                    </li>
                                </ul>
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
                            <h5 class="modal-title" id="helpModalLabel">Ajuda - Informações</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Na aba de informações é onde ficarão arquivos de ajuda, como é caso dos "Arquivos Instrucionais", que individualmente
                                cada arquivo passa informações necessárias sobre o processo de transfusão. 
                                <!-- as outras abas de informações ficaram alocados arquivos situacionais. -->
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>

    <script>
        // // Função para exibir os arquivos selecionados
        // function displaySelectedFiles() {
        //     // Seleciona o input de arquivo
        //     var fileInput = document.getElementById('fileInput');

        //     // Seleciona a lista onde os arquivos serão exibidos
        //     var fileList = document.getElementById('fileList');

        //     // Limpa a lista antes de adicionar os novos arquivos
        //     fileList.innerHTML = '';

        //     // Verifica se pelo menos um arquivo foi selecionado
        //     if (fileInput.files.length === 0) {
        //         return;
        //     }

        //     // Itera sobre os arquivos selecionados
        //     for (var i = 0; i < fileInput.files.length; i++) {
        //         // Cria um novo elemento de lista
        //         var listItem = document.createElement('li');

        //         // Cria um link para o arquivo PDF
        //         var link = document.createElement('a');

        //         link.href        = URL.createObjectURL(fileInput.files[i]);
        //         link.textContent = fileInput.files[i].name;
        //         listItem.appendChild(link);

        //         // Adiciona um botão de exclusão
        //         var deleteButton         = document.createElement('button');
        //         deleteButton.textContent = 'Excluir';
        //         deleteButton.className   = 'btn btn-danger btn-sm mx-1';

        //         deleteButton.onclick = function() {
        //             fileList.removeChild(listItem);
        //             saveFilesToLocalStorage();
        //             displaySelectedFiles();
        //         };

        //         listItem.appendChild(deleteButton);

        //         // Adiciona um botão de alteração
        //         var changeButton         = document.createElement('button');
        //         changeButton.textContent = 'Alterar';
        //         changeButton.className   = 'btn btn-primary btn-sm mx-1';

        //         changeButton.onclick = function() {
        //             fileInput.click(); // Abre o seletor de arquivo
        //         };

        //         listItem.appendChild(changeButton);

        //         // Adiciona o item da lista à lista
        //         fileList.appendChild(listItem);
        //     }

        //     // Salva os arquivos selecionados no localStorage
        //     saveFilesToLocalStorage();
        // }

        // // Função para salvar os arquivos selecionados no localStorage
        // function saveFilesToLocalStorage() {
        //     var files     = [];
        //     var fileList  = document.getElementById('fileList');
        //     var listItems = fileList.getElementsByTagName('li');

        //     for (var i = 0; i < listItems.length; i++) {
        //         files.push(listItems[i].getElementsByTagName('a')[0].textContent);
        //     }

        //     localStorage.setItem('selectedFiles', JSON.stringify(files));
        // }

        // // Função para carregar os arquivos selecionados do localStorage
        // function loadFilesFromLocalStorage() {
        //     var files = JSON.parse(localStorage.getItem('selectedFiles'));

        //     if (files) {
        //         var fileList = document.getElementById('fileList');

        //         for (var i = 0; i < files.length; i++) {
        //             var listItem = document.createElement('li');
        //             var link     = document.createElement('a');

        //             link.href        = '#'; // O link não será funcional após recarregar a página
        //             link.textContent = files[i];
        //             listItem.appendChild(link);

        //             // Adiciona um botão de exclusão
        //             var deleteButton         = document.createElement('button');
        //             deleteButton.textContent = 'Excluir';
        //             deleteButton.className   = 'btn btn-danger btn-sm mx-1';

        //             deleteButton.onclick = function() {
        //                 fileList.removeChild(listItem);
        //                 saveFilesToLocalStorage();
        //                 displaySelectedFiles();
        //             };

        //             listItem.appendChild(deleteButton);

        //             // Adiciona um botão de alteração
        //             var changeButton         = document.createElement('button');
        //             changeButton.textContent = 'Alterar';
        //             changeButton.className   = 'btn btn-primary btn-sm mx-1';

        //             changeButton.onclick = function() {
        //                 fileInput.click(); // Abre o seletor de arquivo
        //             };
                    
        //             listItem.appendChild(changeButton);

        //             // Adiciona o item da lista à lista
        //             fileList.appendChild(listItem);
        //         }
        //     }
        // }

        // Adiciona um ouvinte de evento para o evento change no input de arquivo
        // document.getElementById('fileInput').addEventListener('change', displaySelectedFiles);

        // Carrega os arquivos selecionados do localStorage quando a página é carregada
        // window.addEventListener('load', loadFilesFromLocalStorage);
    </script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>

</body>
</html>