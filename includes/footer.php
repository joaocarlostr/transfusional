<!DOCTYPE html>
<html lang="pt-br">

<head>

  <!-- jQuery do Footer -->
  <?php
    // Verifica se o jQuery já foi incluído
    if (!function_exists('jQuery')) {
      // Se não foi incluído, inclui o jQuery
      echo '<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>';
    }
  ?>

  <script>
    // Use o jQuery do Footer e atribua a uma variável específica
    var footerJQuery = jQuery.noConflict(true);

    // Agora, você pode usar a variável footerJQuery para as funções específicas no footer
    footerJQuery(document).ready(function(){
      // Código específico do Footer aqui
      var didScroll;
      var lastScrollTop = 0;
      var delta         = 5;
      var navbarHeight  = footerJQuery('footer').outerHeight();

      footerJQuery(window).scroll(function(event){
        didScroll = true;
      });

      setInterval(function() {
        if (didScroll) {
          hasScrolled();
          didScroll = false;
        }
      }, 250);

      function hasScrolled() {
        var st = footerJQuery(this).scrollTop();

        if(Math.abs(lastScrollTop - st) <= delta)
          return;

        if (st > lastScrollTop && st > navbarHeight){
          footerJQuery('footer').removeClass('nav-down').addClass('nav-up');
        } else {
          if(st + footerJQuery(window).height() < footerJQuery(document).height()) {
            footerJQuery('footer').removeClass('nav-up').addClass('nav-down');
          }
        }

        lastScrollTop = st;
      }

      // Função para verificar se há scroll vertical
      function hasVerticalScroll() {
        return document.body.offsetHeight > window.innerHeight;
      }

      // Verifica se há scroll vertical
      if (!hasVerticalScroll()) {
        // Se não houver scroll vertical, fixe o footer no final da página
        footerJQuery('footer').css({ 'position': 'fixed', 'bottom': '0' });
      }
    });
  </script>
  <!-- Fim da Seção para o jQuery específico do Footer -->

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="css/style.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <title>Footer</title>

  <!-- <style> -->
    <!-- /* Estilos para o footer */
    /* .footer{
      width: 100%;
      height: 50px;
      background-color: #D9D9D9;
      text-align: center;
      padding: 5px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      font-family: 'Montserrat', sans-serif;
    }

    .footer a {
      color: #000000;
      text-decoration: none;
      margin: 0 10px;
    }

    .footer p {
      color: #000000;
    }

    .footer a:hover {
      opacity: 0.6;
    }

    .footer ul.list-inline {
      margin-bottom: 0;
    }

    .nav-down {
      bottom: -50px;
    } */ -->
  <!-- /* </style> */ -->

</head>
<body>

  <footer class="footer mt-5 nav-down">
    <div class="container">
      <div class="row">
        <div class="col-md-6 text-md-start">
          <p class="mt-2">&copy; <?php echo date('Y'); ?> HUM/UEM. Todos os direitos reservados.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <ul class="list-inline">
            <li class="list-inline-item mt-2"><a href="#" data-bs-toggle="modal" data-bs-target="#termosModal">Termos de Uso</a></li>
            <li class="list-inline-item mt-2"><a href="#" data-bs-toggle="modal" data-bs-target="#privacidadeModal">Política de Privacidade</a></li>
            <li class="list-inline-item mt-2"><a href="#" data-bs-toggle="modal" data-bs-target="#contatoModal">Contato</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>


  <!-- Modal de Termos de Uso -->
  <div class="modal fade" id="termosModal" tabindex="-1" aria-labelledby="termosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="termosModalLabel">Termos de Uso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div style="height:500px; overflow:auto; margin-top:20px;">
            <!-- Conteúdo dos Termos de Uso -->
            <h5><span style="color: rgb(68, 68, 68);">1. Termos</span></h5><p><span style="color: rgb(68, 68, 68);">Ao acessar ao site <a href="http://186.233.152.78/shi/" target="_blank">Sistema Transfusional do HUM</a>, concorda em cumprir estes termos de serviço, todas as leis e regulamentos aplicáveis ​​e concorda que é responsável pelo cumprimento de todas as leis locais aplicáveis. Se você não concordar com algum desses termos, está proibido de usar ou acessar este site. Os materiais contidos neste site são protegidos pelas leis de direitos autorais e marcas comerciais aplicáveis.</span></p>
            <h5><span style="color: rgb(68, 68, 68);">2. Uso de Licença</span></h5><p><span style="color: rgb(68, 68, 68);">É concedida permissão para baixar temporariamente uma cópia dos materiais (informações ou software) no site Sistema Transfusional do HUM , apenas para visualização transitória pessoal e não comercial. Esta é a concessão de uma licença, não uma transferência de título e, sob esta licença, você não pode:&nbsp;</span></p><ol><li><span style="color: rgb(68, 68, 68);">modificar ou copiar os materiais;&nbsp;</span></li><li><span style="color: rgb(68, 68, 68);">usar os materiais para qualquer finalidade comercial ou para exibição pública (comercial ou não comercial);&nbsp;</span></li><li><span style="color: rgb(68, 68, 68);">tentar descompilar ou fazer engenharia reversa de qualquer software contido no site Sistema Transfusional do HUM;&nbsp;</span></li><li><span style="color: rgb(68, 68, 68);">remover quaisquer direitos autorais ou outras notações de propriedade dos materiais; ou&nbsp;</span></li><li><span style="color: rgb(68, 68, 68);">transferir os materiais para outra pessoa ou 'espelhe' os materiais em qualquer outro servidor.</span></li></ol><p><span style="color: rgb(68, 68, 68);">Esta licença será automaticamente rescindida se você violar alguma dessas restrições e poderá ser rescindida por Sistema Transfusional do HUM a qualquer momento. Ao encerrar a visualização desses materiais ou após o término desta licença, você deve apagar todos os materiais baixados em sua posse, seja em formato eletrónico ou impresso.</span></p>
            <h5><span style="color: rgb(68, 68, 68);">3. Isenção de responsabilidade</span></h5><ol><li><span style="color: rgb(68, 68, 68);">Os materiais no site da Sistema Transfusional do HUM são fornecidos 'como estão'. Sistema Transfusional do HUM não oferece garantias, expressas ou implícitas, e, por este meio, isenta e nega todas as outras garantias, incluindo, sem limitação, garantias implícitas ou condições de comercialização, adequação a um fim específico ou não violação de propriedade intelectual ou outra violação de direitos.</span></li><li><span style="color: rgb(68, 68, 68);">Além disso, o Sistema Transfusional do HUM não garante ou faz qualquer representação relativa à precisão, aos resultados prováveis ​​ou à confiabilidade do uso dos materiais em seu site ou de outra forma relacionado a esses materiais ou em sites vinculados a este site.</span></li></ol>
            <h5><span style="color: rgb(68, 68, 68);">4. Limitações</span></h5><p><span style="color: rgb(68, 68, 68);">Em nenhum caso o Sistema Transfusional do HUM ou seus fornecedores serão responsáveis ​​por quaisquer danos (incluindo, sem limitação, danos por perda de dados ou lucro ou devido a interrupção dos negócios) decorrentes do uso ou da incapacidade de usar os materiais em Sistema Transfusional do HUM, mesmo que Sistema Transfusional do HUM ou um representante autorizado da Sistema Transfusional do HUM tenha sido notificado oralmente ou por escrito da possibilidade de tais danos. Como algumas jurisdições não permitem limitações em garantias implícitas, ou limitações de responsabilidade por danos conseqüentes ou incidentais, essas limitações podem não se aplicar a você.</span></p>
            <h5><span style="color: rgb(68, 68, 68);">5. Precisão dos materiais</span></h5><p><span style="color: rgb(68, 68, 68);">Os materiais exibidos no site da Sistema Transfusional do HUM podem incluir erros técnicos, tipográficos ou fotográficos. Sistema Transfusional do HUM não garante que qualquer material em seu site seja preciso, completo ou atual. Sistema Transfusional do HUM pode fazer alterações nos materiais contidos em seu site a qualquer momento, sem aviso prévio. No entanto, Sistema Transfusional do HUM não se compromete a atualizar os materiais.</span></p>
            <h5><span style="color: rgb(68, 68, 68);">6. Links</span></h5><p><span style="color: rgb(68, 68, 68);">O Sistema Transfusional do HUM não analisou todos os sites vinculados ao seu site e não é responsável pelo conteúdo de nenhum site vinculado. A inclusão de qualquer link não implica endosso por Sistema Transfusional do HUM do site. O uso de qualquer site vinculado é por conta e risco do usuário.</span></p><p><br></p>
            <h6><span style="color: rgb(68, 68, 68);">Modificações</span></h6><p><span style="color: rgb(68, 68, 68);">O Sistema Transfusional do HUM pode revisar estes termos de serviço do site a qualquer momento, sem aviso prévio. Ao usar este site, você concorda em ficar vinculado à versão atual desses termos de serviço.</span></p>
            <h6><span style="color: rgb(68, 68, 68);">Lei aplicável</span></h6><p><span style="color: rgb(68, 68, 68);">Estes termos e condições são regidos e interpretados de acordo com as leis do Sistema Transfusional do HUM e você se submete irrevogavelmente à jurisdição exclusiva dos tribunais naquele estado ou localidade.</span></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Política de Privacidade -->
  <div class="modal fade" id="privacidadeModal" tabindex="-1" aria-labelledby="privacidadeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="privacidadeModalLabel">Política de Privacidade</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div style="height:500px; overflow:auto; margin-top:20px;">
            <!-- Conteúdo da Política de Privacidade -->
            <p><span style="color: rgb(68, 68, 68);">A sua privacidade é importante para nós. É política do Sistema Transfusional do HUM respeitar a sua privacidade em relação a qualquer informação sua que possamos coletar no site <a href="http://186.233.152.78/shi/" target="_blank">Sistema Transfusional do HUM</a>, e outros sites que possuímos e operamos.</span></p>
            <p><span style="color: rgb(68, 68, 68);">Solicitamos informações pessoais apenas quando realmente precisamos delas para lhe fornecer um serviço. Fazemo-lo por meios justos e legais, com o seu conhecimento e consentimento. Também informamos por que estamos coletando e como será usado.</span></p><p><span style="color: rgb(68, 68, 68);">Apenas retemos as informações coletadas pelo tempo necessário para fornecer o serviço solicitado. Quando armazenamos dados, protegemos dentro de meios comercialmente aceitáveis ​​para evitar perdas e roubos, bem como acesso, divulgação, cópia, uso ou modificação não autorizados.</span></p>
            <p><span style="color: rgb(68, 68, 68);">Não compartilhamos informações de identificação pessoal publicamente ou com terceiros, exceto quando exigido por lei.</span></p><p><span style="color: rgb(68, 68, 68);">O nosso site pode ter links para sites externos que não são operados por nós. Esteja ciente de que não temos controle sobre o conteúdo e práticas desses sites e não podemos aceitar responsabilidade por suas respectivas políticas de privacidade</a><span style="color: rgb(68, 68, 68);">.</span></p>
            <p><span style="color: rgb(68, 68, 68);">Você é livre para recusar a nossa solicitação de informações pessoais, entendendo que talvez não possamos fornecer alguns dos serviços desejados.</span></p><p><span style="color: rgb(68, 68, 68);">O uso continuado de nosso site será considerado como aceitação de nossas práticas em torno de privacidade e informações pessoais. Se você tiver alguma dúvida sobre como lidamos com dados do usuário e informações pessoais, entre em contacto connosco.</span></p><p><span style="color: rgb(68, 68, 68);"></span></p>
            <h5><span style="color: rgb(68, 68, 68);">Compromisso do Usuário</span></h5><p><span style="color: rgb(68, 68, 68);">O usuário se compromete a fazer uso adequado dos conteúdos e da informação que o Sistema Transfusional do HUM oferece no site e com caráter enunciativo, mas não limitativo:</span></p><ul><li><span style="color: rgb(68, 68, 68);">A) Não se envolver em atividades que sejam ilegais ou contrárias à boa fé a à ordem pública;</span></li><li><span style="color: rgb(68, 68, 68);">B) Não difundir propaganda ou conteúdo de natureza racista, xenofóbica, </span><span style="color: rgb(33, 37, 41);"></span><span style="color: rgb(68, 68, 68);"> ou azar, qualquer tipo de pornografia ilegal, de apologia ao terrorismo ou contra os direitos humanos;</span></li><li><span style="color: rgb(68, 68, 68);">C) Não causar danos aos sistemas físicos (hardwares) e lógicos (softwares) do Sistema Transfusional do HUM, de seus fornecedores ou terceiros, para introduzir ou disseminar vírus informáticos ou quaisquer outros sistemas de hardware ou software que sejam capazes de causar danos anteriormente mencionados.</span></li></ul>
            <h5><span style="color: rgb(68, 68, 68);">Mais informações</span></h5><p><span style="color: rgb(68, 68, 68);">Esperemos que esteja esclarecido e, como mencionado anteriormente, se houver algo que você não tem certeza se precisa ou não, geralmente é mais seguro deixar os cookies ativados, caso interaja com um dos recursos que você usa em nosso site.</span></p><p><span style="color: rgb(68, 68, 68);">Esta política é efetiva a partir de Maio de 2024</span></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Contato -->
  <div class="modal fade" id="contatoModal" tabindex="-1" aria-labelledby="contatoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="contatoModalLabel">Contato</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <!-- Conteúdo do formulário de contato -->
          <p>Sistema Transfusional do HUM - Núcleo de Informática</p>
          <p>Fone: (44) 3011-9100</p>
          <p>Av. Mandacaru, 1590 - Maringá - PR, CEP 87083-240</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Estilos CSS personalizados para os modais -->
  <!-- <style>
    .modal-content {
      background-color: #f8f9fa;
    }

    .modal-header {
      background-color: #d6ecef;
      color: #000000;
    }

    .modal-body {
      color: #333333;
    }

    .modal-title {
      font-weight: bold;
    }

    .form-control {
      border-radius: 0;
    }

    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      border-radius: 10px;
    }

    .btn-primary:hover {
      background-color: #0069d9;
      border-color: #0062cc;
    }
  </style> -->

  <!-- Inclua os arquivos JavaScript do Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <?php
    // Verifica se o jQuery já foi incluído
    if (function_exists('jQuery')) {
      // Se foi incluído, mostra uma mensagem
      echo '<p>O jQuery já foi carregado.</p>';
    }
  ?>

</body>
</html>