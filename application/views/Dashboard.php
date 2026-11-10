<!DOCTYPE html>

<?php
  include_once "application/helpers/function_helper.php";

  $mktSaldo      = count( $mkt )     > 0 ? Functions::MoneyFormat($mkt[0]['Saldo'] )         : 0;
  $comissao      = count( $comissao) > 0 ? Functions::MoneyFormat($comissao[0]['Comissao'] ) : 0;
  $clienteQuant  = count( $cliente)  > 0 ? $cliente[0]['Quantidade']     : 0;
  $vencida       = count( $cobranca) > 0 ? $cobranca[0]['Vencida']       : 0;
  $vencer        = count( $cobranca) > 0 ? $cobranca[0]['Vencer']        : 0;
  $cobrancaTotal = $vencida + $vencer;
  $defaultPerc   = $vencida > 0 && $vencer == 0 ? 100 : 0;
  $defaultPerc   = $vencida == 0 && $vencer > 0 ? 0 : $defaultPerc;
  $vencidaPerc   = floor($cobrancaTotal > 0 && $vencer > 0 ? ($vencida / $cobrancaTotal) * 100 : $defaultPerc  );
  $vencidaGui    = Functions::FloatFormat( $vencida );
  $vencerPerc    = 100 - $vencidaPerc;

?>

<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>ForçaV - Dashboard</title>

  <!-- Custom fonts for this template-->
  <link href="/forcavws/vendors/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="/forcavws/css/sb-admin-2.min.css" rel="stylesheet">
  <link href="/forcavws/css/idealbrasil-footer.css?v=1" rel="stylesheet">

</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- Page Heading -->
          <div class="d-sm-flex align-items-center justify-content-between mb-2 mt-2">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
          </div>

          <!-- Content Row -->
          <div class="row">

            <!-- Saldo MKT Card Example -->
            <div class="col-xl-3 col-md-6 mb-2">
              <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo MKT</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $mktSaldo ;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Comissão (Mês corrente) Card Example -->
            <div class="col-xl-3 col-md-6 mb-2">
              <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Comissão (Mês corrente)</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $comissao; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cobranças (Vencidas e Vencer) Card Example -->
            <div class="col-xl-3 col-md-6 mb-2">
              <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cobranças</div>
                      <div class="row no-gutters align-items-center">
                        <div class="col">
                          <div class="progress mr-2 ">
                            <div class="progress-bar bg-danger progress-bar-striped" role="progressbar" style=<?php echo '"width: '.$vencidaPerc.'%"'; ?> aria-valuemin="0" aria-valuemax="100"><?php echo $vencidaPerc;?>%</div>
                            <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" style=<?php echo '"width: '.$vencerPerc.'%"'; ?> aria-valuemin="0" aria-valuemax="100"><?php echo $vencerPerc;?>%</div>
                          </div>
                        </div>
                      </div>
                    </div>                    
                    <div class="col-auto">
                      <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                  </div>
                  <div class="mt-3 text-xs font-weight-bold text-danger text-uppercase">R$<?php echo $vencidaGui; ?></div>
                </div>
              </div>
            </div>

            <!-- Clientes não Positivados (últimos N mêses) Card Example -->
            <div class="col-xl-3 col-md-6 mb-2">
				<a style="text-decoration: none;" href="ClienteNPositivado?id_repres=<?php echo $id_repres; ?>&id_equipe=0&months=<?php echo $months; ?>">
              		<div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Clientes não Positivados</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $clienteQuant; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                  </div>
                  <div class="mt-3 text-xs font-weight-bold text-danger text-uppercase">Últimos <?php echo $months; ?> mês(es)</div>

                </div>
              </div>
				</a>
            </div>

		  </div>

          <!-- Content Row -->
          <div class="row">

            <!-- Area Chart -->
            <div class="col-xl-8 col-lg-7">
              <div class="card shadow mb-2">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Evolução (global)</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  <div class="chart-area">
                    <canvas id="chart-evolucao"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-2">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Objetivo</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  <div class="h-100 ">
                    <canvas class="w-100" id="chart-objetivo" data-value=<?php echo $realizadoPerc; ?>></canvas>
                      <div class="goal-wrapper">
                        <span id="gauge-text" class="gauge-value float-left"></span>
                        <span class="gauge-value float-left">%</span>
                        <span id="goal-text" class="goal-value float-right">100%</span>
                      </div>
                  </div>

                  <div class="goal-wrapper mt-5">
                    <div class="text-xs font-weight-bold text-danger text-uppercase float-left"><?php echo Functions::MoneyFormat($realizado); ?></div>
                    <div class="text-xs font-weight-bold text-danger text-uppercase float-right"><?php echo Functions::MoneyFormat($objetivo); ?></div>
                  </div>

                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <?php $this->load->view('partials/idealbrasil_footer', array('footer_class' => 'sticky-footer bg-white')); ?>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- JQuery core JavaScript-->
  <script src="/forcavws/vendors/jquery/jquery.min.js"></script>

  <!-- gauge.js -->
  <script src="/forcavws/vendors/gauge.js/dist/gauge.min.js"></script>  

  <!-- Page level plugins -->
  <script src="/forcavws/vendors/chart.js/Chart.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="/forcavws/js/chart-evolucao.js?v=1.10"></script>
  <script src="/forcavws/js/chart-objetivo.js?v=1.2"></script>

</body>

</html>
