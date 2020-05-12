<div class="smartpay smartpay_pricing" id="smartpay_pricing_section">
	<div class="form-row">
		<div class="col-5">
			<div class="form-group">
				<label for="option_name[0]" class="text-muted my-2 d-block"><strong>Base price</strong></label>
				<input type="text" class="form-control" id="option_name[0]" name="option_name[0]">
			</div>
		</div>
		<div class="col-5">
			<div class="form-group">
				<label for="option_name[1]" class="text-muted my-2 d-block"><strong>Sales price</strong></label>
				<input type="text" class="form-control" id="option_name[1]" name="option_name[1]">
			</div>
		</div>
		<div class="col">
			<div class="form-group">
				<label for="option_name[3]" class="text-muted my-2 d-block"><strong>Recurring</strong></label>
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="customSwitch1">
					<label class="custom-control-label" for="customSwitch1">Recurring</label>
				</div>
			</div>
		</div>
	</div>

	<?php include 'variants.php'; ?>
</div>
