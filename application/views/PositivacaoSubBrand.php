<?php
	include_once "application/helpers/function_helper.php";
?>

<html>
	<?php include_once "includes/include-head.php"; ?>
	<body>

		<div class="x_panel">

			<div class="x_title">
				<h5>Positivação Sub Brand</h5>
				<div class="clearfix"></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped jambo_table">
					<thead>
						<tr>
							<th>Sub Brand</th>
							<th class="text-right">Valor</th>
							<th class="text-right">Peso</th>
							<th class="text-right">Clientes Atendidos</th>
							<th class="text-right">Clientes Cadastrados</th>
							<th class="text-right">Unidades</th>
							<th class="text-right">Variedades</th>
							<th class="text-right">Positivação %</th>

						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $item):?>
							<tr>
								<td class="text-nowrap"><?php echo $item['Descricao'];?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Valor'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Peso'] ) ;?></td>
								<td class="text-right"><?php echo $item['ClienteAtend'];?></td>
								<td class="text-right"><?php echo $item['ClienteCad'];?></td>
								<td class="text-right"><?php echo Functions::IntegerFormat( $item['Unidade'] ); ?></td>
								<td class="text-right"><?php echo $item['Variedade'] ; ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Positivacao'] ) ; ?></td>
							</tr>
						<?php endforeach;?>
						<tfoot>
							<tr>
								<th>TOTALIZAÇÃO</th>
								<td class="text-right"><?php echo Functions::FloatFormat( $total['Valor'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $total['Peso'] ) ;?></td>
								<th></th>
								<th></th>
								<td class="text-right"><?php echo Functions::FloatFormat( $total['Unidade'] ) ;?></td>
								<th></th>
								<th></th>
								</tr>
						</tfoot>
					</tbody>
				</table>
			</div>

		</div>

	</body>
</html>  