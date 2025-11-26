<?php
    include "database.php";
    include "function.php";
    

    // --- QUERIES PARA O DASHBOARD ---

    // 1. Total de Bolsas Transfundidas no Mês Atual
    $query_bolsas_mes = "SELECT COUNT(*) as total FROM sth_cadastro_bolsa 
                         WHERE EXTRACT(MONTH FROM data_transfusao) = EXTRACT(MONTH FROM CURRENT_DATE) 
                         AND EXTRACT(YEAR FROM data_transfusao) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $result_bolsas_mes = conecta_query($conexao, $query_bolsas_mes);
    $row_bolsas_mes = pg_fetch_assoc($result_bolsas_mes);
    $total_bolsas_mes = $row_bolsas_mes['total'];

    // 2. Total de Bolsas em Reserva
    $query_reserva = "SELECT COUNT(*) as total FROM sth_cadastro_bolsa WHERE reserva = 'sim'";
    $result_reserva = conecta_query($conexao, $query_reserva);
    $row_reserva = pg_fetch_assoc($result_reserva);
    $total_reserva = $row_reserva['total'];

    // 3. Reações Transfusionais no Mês Atual
    $query_reacoes_mes = "SELECT COUNT(*) as total FROM sth_reacoes_transfusionais 
                          WHERE EXTRACT(MONTH FROM data) = EXTRACT(MONTH FROM CURRENT_DATE) 
                          AND EXTRACT(YEAR FROM data) = EXTRACT(YEAR FROM CURRENT_DATE)";
    $result_reacoes_mes = conecta_query($conexao, $query_reacoes_mes);
    $row_reacoes_mes = pg_fetch_assoc($result_reacoes_mes);
    $total_reacoes_mes = $row_reacoes_mes['total'];

    // 4. Não Conformidades Ativas
    $query_nc = "SELECT COUNT(*) as total FROM sth_nao_conformidade WHERE status = 'ativo'";
    $result_nc = conecta_query($conexao, $query_nc);
    $row_nc = pg_fetch_assoc($result_nc);
    $total_nc = $row_nc['total'];

    // 5. Dados para Gráfico de Hemocomponentes (Pizza)
    $query_graf_hemo = "SELECT h.sigla, COUNT(cb.id_bolsa) as qtd
                        FROM sth_cadastro_bolsa cb
                        JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                        GROUP BY h.sigla";
    $result_graf_hemo = conecta_query($conexao, $query_graf_hemo);
    $labels_hemo = [];
    $data_hemo = [];
    while ($row = pg_fetch_assoc($result_graf_hemo)) {
        $labels_hemo[] = $row['sigla'];
        $data_hemo[] = $row['qtd'];
    }

    // 6. Dados para Gráfico de Transfusões nos Últimos 7 Dias (Barras)
    $query_graf_transf = "SELECT to_char(data_transfusao, 'DD/MM') as data_fmt, COUNT(*) as qtd
                          FROM sth_cadastro_bolsa
                          WHERE data_transfusao >= CURRENT_DATE - INTERVAL '7 days'
                          GROUP BY data_transfusao
                          ORDER BY data_transfusao";
    $result_graf_transf = conecta_query($conexao, $query_graf_transf);
    $labels_transf = [];
    $data_transf = [];
    while ($row = pg_fetch_assoc($result_graf_transf)) {
        $labels_transf[] = $row['data_fmt'];
        $data_transf[] = $row['qtd'];
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>Dashboard Transfusional - HUM</title>

    <style>
        body {
            background: #f4f6f9 !important;
            background-image: none !important;
            display: block !important;
            height: auto !important;
            padding-top: 100px;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .kpi-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            height: 100%;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .kpi-card.blue::before { background: #3498db; }
        .kpi-card.green::before { background: #2ecc71; }
        .kpi-card.red::before { background: #e74c3c; }
        .kpi-card.orange::before { background: #f39c12; }

        .kpi-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .kpi-card.blue .kpi-icon-wrapper { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .kpi-card.green .kpi-icon-wrapper { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .kpi-card.red .kpi-icon-wrapper { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .kpi-card.orange .kpi-icon-wrapper { background: rgba(243, 156, 18, 0.1); color: #f39c12; }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .kpi-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-wrapper {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 380px;
            margin-bottom: 20px;
        }
        
        .chart-title {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-left: 10px;
            border-left: 4px solid #34495e;
        }
    </style>

</head>

<body>
    <?php include_once "includes/header.php"; ?>

    <div class="dashboard-container">
        <div class="row">
            <div class="col-12">
                <h2 class="page-title">
                    <i class="fas fa-chart-pie me-2"></i>Dashboard Transfusional
                </h2>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card blue">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-value"><?php echo $total_bolsas_mes; ?></div>
                            <div class="kpi-label">Transfusões (Mês)</div>
                        </div>
                        <div class="kpi-icon-wrapper">
                            <i class="fas fa-procedures"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card green">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-value"><?php echo $total_reserva; ?></div>
                            <div class="kpi-label">Bolsas em Reserva</div>
                        </div>
                        <div class="kpi-icon-wrapper">
                            <i class="fas fa-archive"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card red">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-value"><?php echo $total_reacoes_mes; ?></div>
                            <div class="kpi-label">Reações Adversas</div>
                        </div>
                        <div class="kpi-icon-wrapper">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card orange">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-value"><?php echo $total_nc; ?></div>
                            <div class="kpi-label">Não Conformidades</div>
                        </div>
                        <div class="kpi-icon-wrapper">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="chart-wrapper">
                    <div class="chart-title">Distribuição por Hemocomponente</div>
                    <canvas id="chartHemocomponentes"></canvas>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="chart-wrapper">
                    <div class="chart-title">Evolução de Transfusões (7 Dias)</div>
                    <canvas id="chartTransfusoes"></canvas>
                </div>
            </div>
        </div>

        <?php include_once "includes/footer.php"; ?>

    </div>

    <script>
        const ctxHemo = document.getElementById('chartHemocomponentes').getContext('2d');
        new Chart(ctxHemo, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels_hemo); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_hemo); ?>,
                    backgroundColor: [
                        '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#34495e', '#95a5a6'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 8,
                            font: {
                                size: 10
                            },
                            boxWidth: 12,
                            boxHeight: 12
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 5,
                        bottom: 60,
                        left: 10,
                        right: 10
                    }
                }
            }
        });

        const ctxTransf = document.getElementById('chartTransfusoes').getContext('2d');
        new Chart(ctxTransf, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_transf); ?>,
                datasets: [{
                    label: 'Transfusões',
                    data: <?php echo json_encode($data_transf); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5,
                    borderSkipped: false
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#f0f0f0'
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5,
                        left: 5,
                        right: 5
                    }
                }
            }
        });
    </script>

</body>
</html>