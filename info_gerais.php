<?php
    include __DIR__ . "/database.php";
    include __DIR__ . "/function.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="css/style.css">
    <title>Informações Gerais - HUM</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        .container-crud { max-width: 1400px; margin: 90px auto 120px auto; padding: 0 20px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; margin-bottom: 30px; }
        .card-header-crud { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: #fff !important; padding: 20px 30px; font-size: 1.3rem; font-weight: 600; } /* Teal/Cyan for Info */
        .card-body-crud { padding: 40px; }

        .info-text { font-size: 1.05rem; line-height: 1.8; color: #555; text-align: justify; }
        
        .list-group-item { border: none; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; transition: background-color 0.2s; }
        .list-group-item:hover { background-color: #f8f9fa; }
        .list-group-item:last-child { border-bottom: none; }
        
        .pdf-link { text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; }
        .pdf-link:hover { text-decoration: none; color: #17a2b8; }
        .pdf-icon { color: #dc3545; font-size: 1.5rem; margin-right: 15px; width: 25px; text-align: center; }
        .ext-icon { color: #28a745; font-size: 1.3rem; margin-right: 15px; width: 25px; text-align: center; } /* Green for external links */
        
        .section-title { font-size: 1.1rem; color: #17a2b8; font-weight: 700; margin-bottom: 15px; text-transform: uppercase; border-left: 4px solid #17a2b8; padding-left: 10px; }
    </style>
</head>
<body>
    <?php include_once "includes/header.php"; ?>

    <div class="container container-crud">
        
        <!-- Intro Card -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-info-circle mr-2"></i> Sobre o Serviço Transfusional</span>
            </div>
            <div class="card-body card-body-crud">
                <div class="info-text">
                     A Diretoria de Enfermagem (DEE) do HUM organizou o Serviço Transfusional em 2021, visando criar uma ligação sólida entre as atividades no hospital e no Hemocentro. 
                     Com o objetivo de monitorar as transfusões sanguíneas, esclarecer dúvidas, oferecer capacitação e contribuir para a elaboração de protocolos, o serviço também alimenta o 
                     Sistema de Controle Hemoterápico da Vigilância Sanitária da Secretaria de Saúde do Paraná. O Comitê Transfusional, em parceria com o Serviço Transfusional, utiliza estratégias 
                     para destacar a importância do uso adequado de documentos como Requisições Transfusionais, investigar eventos adversos, lidar com não conformidades na administração de 
                     hemocomponentes e monitorar reações adversas. Essas ações têm como objetivo aprimorar a qualidade da assistência prestada e garantir a Segurança Transfusional no ambiente hospitalar.
                </div>
            </div>
        </div>

        <!-- Files Card -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                 <span><i class="fas fa-folder-open mr-2"></i> Biblioteca de Documentos</span>
            </div>
            <div class="card-body card-body-crud">
                <div class="row">
                    <!-- Column 1 -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="section-title">Arquivos Instrucionais</div>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <a href="arquivos/consentimento-info-transfusao.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Consentimento Informado Transfusão
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/formulario-controle-temperatura-geladeira.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Formulário Controle Temp. Geladeira
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/POP-controle-temperatura-geladeira.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> POP Controle Temp. Geladeira
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/reserva-cirurgica.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Reserva Cirúrgica
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="arquivos/sangria-terapeutica.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Sangria Terapêutica
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="arquivos/transfusao-macica.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Transfusão Maciça
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="arquivos/transfusao-sangue.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Transfusão de Sangue
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="arquivos/DHC - formulário de devolução de hemocomponentes.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Formulário Devolução de Hemocomponentes
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="arquivos/RT requisição de transfusão.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Requisição de Transfusão
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="arquivos/CSV-REV-06-controle-sinais-vitais.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Controle de Sinais Vitais
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Column 2 -->
                    <div class="col-lg-6">
                        <div class="section-title">Arquivos Adicionais & Links Utéis</div>
                        <ul class="list-group">
                             <li class="list-group-item">
                                <a href="arquivos/CICLO DO SANGUE - ATO TRANSFUSIONAL.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Ciclo de Sangue - Ato Transfusional
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/manual_tecnico_hemovigilancia_08112007.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Manual Técnico de Hemovigilância (2007)
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/manual_hemovigilancia_2022.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Manual Técnico de Hemovigilância (2022)
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/Portaria_Consolidacao_5_28_SETEMBRO_2017.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Portaria de Consolidação Nº 5
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/guia_uso_hemocomponentes_2ed.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Guia para uso de Hemocomponentes
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/PORTARIA Nº 158, DE 4 DE FEVEREIRO DE 2016.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Portaria Nº 158 (2016)
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="arquivos/DOCUMENTO DE ORIENTAÇÕES PARA O SISTEMA.pdf" class="pdf-link" target="_blank">
                                    <i class="far fa-file-pdf pdf-icon"></i> Orientações para o Sistema Transfusional
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="https://www.google.com/intl/pt-br/drive/about.html" class="pdf-link" target="_blank" rel="noopener noreferrer">
                                    <i class="fab fa-google-drive ext-icon"></i> Google Drive
                                </a>
                            </li>
                             <li class="list-group-item">
                                <a href="https://lookerstudio.google.com/navigation/reporting" class="pdf-link" target="_blank" rel="noopener noreferrer">
                                    <i class="far fa-chart-bar ext-icon"></i> Dashboard Comparativos (Looker)
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include_once "includes/footer.php"; ?>
    <script type="text/javascript" src="js/script.js"></script>
</body>
</html>