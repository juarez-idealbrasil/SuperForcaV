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
							<th class="text-right">Venda</th>
							<th class="text-right">Comissão</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $item):?>
							<tr>
								<td class="text-nowrap"><?php echo $item['RazSocial'];?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Venda'] ); ?></td>
								<td class="text-right"><?php echo Functions::FloatFormat( $item['Comissao'] ) ;?></td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>

		</div>

	<?php $this->load->view('partials/idealbrasil_footer'); ?>
	</body>
</html>  