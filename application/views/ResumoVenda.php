<?php
	include_once "application/helpers/function_helper.php";
?>

<html>
	<?php include_once "includes/include-head.php"; ?>
	<body>

		<div class="x_panel">

			<div class="x_title">
				<h5>Resumo de Vendas</h5>
				<div class="clearfix"></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped jambo_table">
					<thead>
						<tr>
							<th>Razão Social</th>
							<th class="text-center">Emissão</th>
							<th class="text-center">Fatura</th>
							<th class="text-center">Valor</th>
							<th class="text-center">Peso</th>
							<th>Condição</th>
							<th>Forma</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $item):?>
							<tr>
								<td class="text-nowrap"><?php echo $item['RazSocial'];?></td>
								<td class="text-nowrap text-center"><?php echo Functions::DateFormat( $item['Emissao'] ); ?></td>
								<td class="text-nowrap text-center"><?php echo Functions::DateFormat( $item['Fatura'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Valor'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Peso'] ) ;?></td>
								<td class="text-nowrap"><?php echo $item['CondPagam'];?></td>
								<td class="text-nowrap"><?php echo $item['FormaPagam'];?></td>
							</tr>
						<?php endforeach;?>
						<tfoot>
							<tr>
								<th>TOTALIZAÇÃO</th>
								<th></th>
								<th></th>
								<td class="text-right"><?php echo Functions::FloatFormat( $total['Valor'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $total['Peso'] ) ;?></td>
								<th></th>
								<th></th>
								<th></th>
								</tr>
						</tfoot>
					</tbody>
				</table>
			</div>

		</div>

	<?php $this->load->view('partials/idealbrasil_footer'); ?>
	</body>
</html>  