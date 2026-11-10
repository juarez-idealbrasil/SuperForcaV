<?php
	include_once "application/helpers/function_helper.php";
?>

<html>
	<?php include_once "includes/include-head.php"; ?>
	<body>

		<div class="x_panel">

			<div class="x_title">
				<h5>Objetivo Brand</h5>
				<div class="clearfix"></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped jambo_table">
					<thead>
						<tr>
							<th>Brand</th>
							<th class="text-right">Objetivo</th>
							<th class="text-right">Realizado</th>
							<th class="text-right">Saldo</th>
							<th class="text-right">Posit %</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $item):?>
							<tr>
								<td class="text-nowrap"><?php echo $item['Descricao'] ; ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Objetivo'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Realizado'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Saldo'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Positivado'] ); ?></td>
							</tr>
						<?php endforeach;?>

						<tfoot>
						<tr>
							<th>Total</th>
							<th class="text-right"><?php echo Functions::FloatFormat( $total['Objetivo'] ) ;?></th>
							<th class="text-right"><?php echo Functions::FloatFormat( $total['Realizado'] ) ;?></th>
							<th class="text-right"><?php echo Functions::FloatFormat( $total['Saldo'] ) ;?></th>
							<th class="text-right"><?php echo Functions::FloatFormat( $total['Positivado'] ) ;?></th>
						</tr>
						</tfoot>
					</tbody>
				</table>
			</div>

		</div>

	<?php $this->load->view('partials/idealbrasil_footer'); ?>
	</body>
</html>  