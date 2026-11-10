<?php
	include_once "application/helpers/function_helper.php";
?>

<html>
	<?php include_once "includes/include-head.php"; ?>
	<body>

		<div class="x_panel">

			<div class="x_title">
				<h5>Ciiente x Brand</h5>
				<div class="clearfix"></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped jambo_table">
					<thead>
						<tr>
						
							<th>CLIENTE</th>
							
							<?php foreach ($body as $customer):

								for ($i=1; $i < count($customer) ; $i++) { 
									
									echo '<th class="text-right">' . $customer[$i]['Descricao'].'</th>';
									
								}

								break;

							endforeach;?>		
							
						</tr>
					</thead>
					<tbody>
						<?php foreach ($body as $customer):?>
							<tr>
								<td class="text-nowrap"><?php echo $customer[0] ; ?></td>
								<?php for ($i=1; $i < count($customer) ; $i++) { ?>
									<td class="text-right"><?php echo Functions::FloatFormat( $customer[$i]['Valor'] ); ?></td>
								<?php }; ?>
							</tr>
						<?php endforeach;?>
					
					</tbody>
				</table>
			</div>

		</div>

	<?php $this->load->view('partials/idealbrasil_footer'); ?>
	</body>
</html>  