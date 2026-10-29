<html>
	<?php
		include_once "includes/include-head.php";
  		include_once "application/helpers/function_helper.php";
	 ?>

	<body>

		<div class="x_panel">

			<div class="x_title">
				<h5>Clientes Não Positivados - <?php echo $months; ?> mês(es)</h5>
				<div class="clearfix"></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped jambo_table">
					<thead>
						<tr>
							<th class="text-right">Código</th>
							<th>Cliente</th>
							<th class="text-center">Último Pedido</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $item):?>
							<tr>
								<td class="text-right"><?php echo $item['Id'] ;?></td>
								<td class="text-nowrap"><?php echo $item['RazSocial'];?></td>
								<td class="text-center"><?php echo Functions::DateFormat( $item['LastOrder'] ) ; ?></td>
							</tr>
						<?php endforeach;?>
						<tfoot>
							<tr>
								<td></td>
								<th>TOTAL</th>
								<td class="text-center"><?php echo $quantity ;?></td>
							</tr>
						</tfoot>
					</tbody>
				</table>
			</div>

		</div>

	</body>
</html>  
