<?php

/*     Tabela de Hemocomponentes

CÓD SIGLA	    DESCRIÇÃO
1	 CH	    Concentrado de Hemácia
2	 CHF	Concentrado de Hemácia Filtrada
3	 CHL	Concentrado de Hemácia Lavada
4	 CHI	Concentrado de Hemácia Irradiada
5	 CHFI	 Concentrado de Hemácia Filtrada e Irradiada
6 	 CHLI	 Concentrado de Hemácia Lavada e Irradiada
7	 PFC	Plasma Fresco Congelado
8	 PCP	Pool de Concentrado de Plaquetas
9	 CPR	Concentrado de Plaquetas Randomizado
10	 CP     AFÉRESE	Concentrado de Plaquetaférese
11	 CRIO	Crioprecipitado
12	 EXSAN	Exsanguineo transfusão
13	 CHR	Concentrado de Hemácia Reserva
14	 PFR	Plasma Fresco Reserva
15	 CPR	Concentrado de Plaqueta Reserva
16	 ST	    Sangria Terapêutica */  


// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

$sql = "SELECT * FROM SHT_emocomponentes"; 

// Executa a consulta
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $CH = $row["CH"];
        $CHF = $row["CHF"];
        $CHL = $row["CHL"];
        $CHI = $row["CHI"];
        $CHFI = $row["CHFI"];
        $CHLI = $row["CHLI"];
        $PFC = $row["PFC"];
        $PCP = $row["PCP"];
        $CPR = $row["CPR"];
        $CP_AFERESE = $row["CP_AFERESE"];
        $CRIO = $row["CRIO"];
        $EXSAN = $row["EXSAN"];
        $CHR = $row["CHR"];
        $PFR = $row["PFR"];
        $CPR_RESERVA = $row["CPR_RESERVA"];
        $ST = $row["ST"];
    }
} else {
    echo "Nenhum resultado encontrado na consulta.";
}

$conn->close();

?>