<?php
    // CONSTRUÇÃO DA PÁGINA DO RELATÓRIO
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Definindo uma classe que estende FPDF
    class PDF extends FPDF {       
        function Footer() {  // Rodapé personalizado
            
            $this->SetY(-15); // Posição: a 15 mm do fim

            // Desenha uma linha separadora
            $this->SetDrawColor(128, 128, 128); // Define a cor da linha como preto
            $this->SetLineWidth(0.2); // Define a largura da linha

            $marginLeft  = $this->lMargin; // Margem esquerda
            $marginRight = $this->w - $this->rMargin; // Largura total da página - margem direita

            $this->Line($marginLeft, $this->GetY(), $marginRight, $this->GetY()); // Desenha a linha
            $this->SetY(-12); // Move para a posição do contador de páginas
            $this->SetFont('Arial', 'I', 8); // Fonte em itálico, tamanho 8
            $this->Cell(70, 10, iconv('utf-8', 'iso-8859-1', 'Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');

            //quadrado com a cor verde
            $this->SetFillColor(1, 200, 1);
            $this->Cell(5, 5, "", 0, 0, 'L', true);
            $this->Cell(15, 5, "Correto", 0, 0, 'L');

            //quadrado com a cor amarela
            $this->SetFillColor(200, 200, 1);
            $this->Cell(5, 5, "", 0, 0, 'L', true);
            $this->Cell(15, 5, "Verificar", 0, 0, 'L');

            //quadrado coma cor branca
            $this->Cell(5, 5, "", 1, 0, 'L', false);
            $this->Cell(15, 5, iconv('utf-8', 'iso-8859-1', 'Não encontrado'), 0, 0, 'L');

            // Número da página
            $this->Cell(0, 10, iconv('utf-8', 'iso-8859-1', 'Página ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        }
    }

    // Cria um novo objeto PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Adiciona uma página paisagem
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE BOLSAS TRANSFUNDIDAS HUM ST X BOLSAS TRANSFUNDIDAS HUM HEMOCENTRO'),0,1,'C');

    $pdf->Cell(100,10, '', 0, 1);

    $pdf->SetFont('Arial', '', 10); // Definindo a fonte e o tamanho
    $pdf->SetFillColor(200); // Define a cor cinza 

    // Cabeçalho da tabela
    $pdf->Cell(15, 10, iconv('utf-8', 'iso-8859-1', 'N°'), 1, 0, 'C', true);
    $pdf->Cell(38, 10, iconv('utf-8', 'iso-8859-1', 'Data/Hora Transfusão'), 1, 0, 'C', true);
    $pdf->Cell(30, 10, iconv('utf-8', 'iso-8859-1', 'Bolsa'), 1, 0, 'C', true);
    $pdf->Cell(25, 10, iconv('utf-8', 'iso-8859-1', 'Número SUS'), 1, 0, 'C', true);
    $pdf->Cell(10, 10, iconv('utf-8', 'iso-8859-1', 'ABO'), 1, 0, 'C', true);
    $pdf->Cell(10, 10, iconv('utf-8', 'iso-8859-1', 'RH'), 1, 0, 'C', true);
    $pdf->Cell(20, 10, iconv('utf-8', 'iso-8859-1', 'Registro'), 1, 0, 'C', true);
    $pdf->Cell(130, 10, iconv('utf-8', 'iso-8859-1', 'Hemocomponente / Atributos'), 1, 1, 'C', true);

    // COMEÇA A IMPORTAR DADOS DO ARQUIVO
    $tamanho_maximo = 1000000;
    $tipos_aceitos  = array("application/vnd.ms-excel","csv", "txt", "TXT", "text/plain", "text/csv");
    $arquivo        = $_FILES['importa_arquivo'];

    //MENSAGENS DE ERRO
    if($arquivo['error'] != 0) {
        $_SESSION['erro_arquivo'] = "erro no arquivo";
        header("Location:relatorio.php");
        exit;
    }

    if($arquivo['size'] == 0 or $arquivo['tmp_name'] == NULL) {
        $_SESSION['erro_arquivo'] = "nenhum arquivo enviado";
        header("Location:relatorio.php");
        // echo '<p><b><font color="red">Nenhum arquivo enviado </font></b></p>';
        exit;
    }

    if($arquivo['size'] > $tamanho_maximo) {
        $_SESSION['erro_arquivo'] = "arquivo grande";
        header("Location:relatorio.php");
        // echo '<p><b><font color="red">O arquivo enviado é muito grande</font></b></p>';
        exit;
    }

    if(array_search($arquivo['type'],$tipos_aceitos) === FALSE) {
        $_SESSION['erro_arquivo'] = "arquivo tipo errado";
        header("Location:relatorio.php");
        // echo '<p><b><font color="red">O arquivo enviado não é do tipo(' .$arquivo['type']. ') aceito para transferencia. O tipo aceito é </font></b></p>';
        // echo '<pre>';
        // print_r($tipos_aceitos);
        // echo '</pre>';
        exit; 
    }

    $destino = $arquivo['name'];
    $destino = "arquivos-csv/$arquivo[name]";

    if(move_uploaded_file($arquivo['tmp_name'], $destino)) {
        // importa os registros
        $arq = fopen ($destino, "r");

        // verifica quantas linhas tem a importar
        $contador_linhas = count (file ($destino));
		$file_lines      = file($destino);

        // altera o timeout do servidor para 60 minutos
        ini_set('max_execution_time', 3600); 

        //inicializa variaveis
        $cont        = 0;
        $qtd_achados = 0;

        for ($x=1; $x<$contador_linhas; $x++) {
            //servidor
            if(!($conexao = pg_connect ("host=186.233.152.78 dbname=shiteste user=postgres password=systemhum")))  {
                echo 'Nao foi possivel conectar ao banco de dados!';
            }

            //banco local
            // if(!($conexao = pg_connect("host=localhost dbname=Transfusional user=postgres password=root")))  {
            //     echo 'Nao foi possivel conectar ao banco de dados!';
            // }
            
            $linha = substr($file_lines[$x],0,500);

            // Pesquisa digito a digito até achar ','
            //--------------------------------------------------------------------------------------------
            // data e hora da transfusão
            $ind = 0;

            while ($linha[$ind] <> ',') {
                $ind++;
            }

            $tam      = $ind;
            $dataHora = substr($linha,0,$tam);
            $dataHora = substr($dataHora,0,16);

            $mes_arquivo = nomeia_mes(substr($dataHora,3,2));
            $ano_arquivo = substr($dataHora,6,4);
            // echo "$dataHora qtd: " . mb_strlen($dataHora) . " tipo: " . gettype($dataHora) . " | ";
        
            //--------------------------------------------------------------------------------------------
            // número da bolsa
            $ind++;
            $pos = $ind;

            while ($linha[$ind] <> ',') {
                $ind++;
            }

            $tam       = $ind - $pos;
            $num_bolsa = substr($linha,$pos,$tam);
            // echo "$num_bolsa qtd: " . mb_strlen($num_bolsa) . " tipo: " . gettype($num_bolsa) . " | ";

            //--------------------------------------------------------------------------------------------
            // número do sus da bolsa
            $aux_sus = 0;

            while($aux_sus < 3){
                $ind++;
                $pos = $ind;
    
                while ($linha[$ind] <> ',') {
                    $ind++;
                }

                $aux_sus++;
            }

            $tam     = $ind - $pos;
            $num_sus = substr($linha,$pos,$tam);
            // echo "$num_sus \n | ";

            //--------------------------------------------------------------------------------------------
            // hemocomponente
            $ind++;
            $pos = $ind;

            while ($linha[$ind] <> ',') {
                $ind++;
            }

            $tam  = $ind - $pos;
            $hemo = substr($linha,$pos,$tam);
            // echo "$hemo | ";

            //--------------------------------------------------------------------------------------------
            // atributo do hemocomponente
            $ind++;
            $pos = $ind;

            while ($linha[$ind] <> ',') {
                $ind++;
            }

            $tam  = $ind - $pos;
            $atri = substr($linha,$pos,$tam);
            // echo "________________________";   

            //--------------------------------------------------------------------------------------------
            // ABO
            $aux_abo = 0;

            while ($aux_abo < 13) {
                $ind++;
                $pos = $ind;

                while ($linha[$ind] <> ',') {
                    $ind++;
                }
                // $tam = $ind - $pos;
                // $abo = substr($linha,$pos,$tam);
                // echo "$abo ________________________";
                $aux_abo++;
            }

            $tam = $ind - $pos;
            $abo = substr($linha,$pos,$tam);
            // echo "$abo ________________________ ind: $ind | ";   

            //--------------------------------------------------------------------------------------------
            // RH
            $ind++;
            $pos = $ind;

            while ($linha[$ind] <> ',') {
                $ind++;
            }

            $tam = $ind - $pos;
            $rh  = substr($linha,$pos,$tam);
            // echo "$rh ________________________ ind: $ind, fim: $linha | ";   

            $rh_extenso = $rh == "N" ? "Negativo" : "Positivo";

            //--------------------------------------------------------------------------------------------
            // registro hospitalar
            $ind++;
            $pos = $ind;

            // $tam                 = $ind - $pos;
            $registro_hospitalar = substr($linha,$pos);
            // echo "registro: $registro_hospitalar ________________________";   
            
            //--------------------------------------------------------------------------------------------

            // Consulta todas as bolsas transfundidas
            $query = conecta_query($conexao, "SELECT cb.num_bolsa, num_sus, horario_inicio, data_transfusao, h.sigla, dp.abo, dp.rh_d, dp.registro 
            FROM sth_cadastro_bolsa cb
            INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
            INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
            INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
            ORDER BY cb.data_transfusao, cb.num_bolsa, cb.num_sus");

            //inicializa variaveis
            $igual          = false;
            $hemocomponente = !empty($atri) ? "$hemo$atri" : $hemo;
            $hemo_sigla     = sigla_hemocomponente($hemocomponente);
            $data_arquivo   = substr($dataHora, 0, 10);
            $bolsa_registro = $sus_registro = $hemo_registro = $registro = null; 

            while($result_query = pg_fetch_assoc($query)){

                $data = date('d/m/Y', strtotime($result_query['data_transfusao'])) . " " . $result_query["horario_inicio"];
                $data = substr($data,0,10);
                
                if($result_query["num_sus"] == $num_sus || $result_query["num_bolsa"] == $num_bolsa ){

                    // echo "------------ ACHEI UMA --------- DATA BD: $data DATA ARQ: $dataHora | ";
                    $pdf->SetFillColor(200, 200, 1); // cor amarelo, aviso
                    $igual      = true;
                    $atri       = null;

                    $registro       = $result_query["registro"];
                    $hemo           = $result_query["sigla"];
                    $bolsa_registro = $result_query["num_bolsa"];
                    $sus_registro   = $result_query["num_sus"];
                    $hemo_registro  = $result_query["sigla"];

                    $qtd_achados++;
                }

                if($result_query["num_sus"]   == $num_sus 
                && $result_query["num_bolsa"] == $num_bolsa
                && $result_query["sigla"]     == $hemo_sigla 
                && $result_query["registro"]  == trim($registro_hospitalar)){
                       
                    // echo "------------ ACHEI UMA --------- DATA BD: $data DATA ARQ: $dataHora | ";
                    $pdf->SetFillColor(1, 200, 1); // cor verde, sucesso
                    $igual = true;
                    $hemo  = $hemo_sigla;
                    $atri  = null;

                    $bolsa_registro = $result_query["num_bolsa"];
                    $sus_registro   = $result_query["num_sus"];
                    $hemo_registro  = $result_query["sigla"];

                    break;
                }
            }

            $cont++;
            
            $pdf->Cell(15, 5, $cont, 1, 0, 'C', $igual);
            $igual_antes = $igual;

            $pdf->Cell(38, 5, iconv('utf-8', 'iso-8859-1', $dataHora), 1, 0, 'C', $igual);
            
            $igual = isset($bolsa_registro) && $bolsa_registro == $num_bolsa ?  true : false;
            $pdf->Cell(30, 5, iconv('utf-8', 'iso-8859-1', $num_bolsa), 1, 0, 'C', $igual);

            $igual = isset($sus_registro) && $sus_registro == $num_sus ? true : false;
            $pdf->Cell(25, 5, iconv('utf-8', 'iso-8859-1', $num_sus), 1, 0, 'C', $igual);

            $igual = $igual_antes;
            $pdf->Cell(10, 5, iconv('utf-8', 'iso-8859-1', $abo), 1, 0, 'C', $igual);

            $pdf->Cell(10, 5, iconv('utf-8', 'iso-8859-1', $rh), 1, 0, 'C', $igual);

            $igual = isset($registro) && $registro == trim($registro_hospitalar) ? true : false;
            $pdf->Cell(20, 5, iconv('utf-8', 'iso-8859-1', $registro_hospitalar), 1, 0, 'C', $igual);

            $igual = isset($hemo_registro) && $hemo_registro == $hemo_sigla ? true : false;
            $pdf->Cell(130, 5, iconv('utf-8', 'iso-8859-1', "Hemocentro: $hemo_sigla => Sistema: $hemo $atri"), 1, 1, 'L', $igual);

            pg_free_result($query);
        }

        fclose($arq);
        // restaura o timeout do servidor para 30 segundos
        ini_restore('max_execution_time');
        // print ' ';
        // print 'IMPORTACAO CONCLUIDA';

        //Saída do PDF para o navegador
        if($qtd_achados > 0){
            $pdf->Output("relatorio_bolsas_transfundidas_ST_vs_Hemocentro $mes_arquivo $ano_arquivo.pdf", 'D');
            
        } else {
            //mostra mensagem de relatorio vazio
            $_SESSION['validado_relatorio_vazio'] = 0;
            header("Location:relatorio.php");
        }
        
    } else {
        echo '<p><b><font color="red">Ocorreu um erro durante a carga do arquivo! </font></b></p>';        
    }