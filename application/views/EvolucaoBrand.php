<?php
	include_once "application/helpers/function_helper.php";
?>

<html>
	<?php include_once "includes/include-head.php"; ?>
	<body>

		<div class="x_panel">

			<div class="x_title">
				<h5>Evolução de Brand</h5>
				<div class="clearfix"></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped jambo_table">
					<thead>
						<tr>
							<th>Brand</th>
							<th class="text-center">Jan</th>
							<th class="text-center">Fev</th>
							<th class="text-center">Mar</th>
							<th class="text-center">Abr</th>
							<th class="text-center">Mai</th>
							<th class="text-center">Jun</th>
							<th class="text-center">Jul</th>
							<th class="text-center">Ago</th>
							<th class="text-center">Set</th>
							<th class="text-center">Out</th>
							<th class="text-center">Nov</th>
							<th class="text-center">Dez</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $item):?>
							<tr>
								<td class="text-nowrap"><?php echo $item['Descricao'] ; ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Janeiro'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Fevereiro'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Marco'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Abril'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Maio'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Junho'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Julho'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Agosto'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Setembro'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Outubro'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Novembro'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Dezembro'] ); ?></td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>

		</div>

	</body>
</html>  