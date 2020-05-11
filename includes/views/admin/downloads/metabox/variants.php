<div class="smartpay smartpay_variant" id="smartpay_variant_section">

	<div class="border rounded bg-light text-center p-4">
		<i data-feather="layers" width="42" height="42"></i>
		<h3>Offer variations of this product</h3>
		<p class="text-muted">Sweeten the deal for your customers with different options for format, version, etc</p>
		<button class="btn btn-light border shadow-sm">Add Variations</button>
	</div>

    <div class="card p-0" id="add_variant_section">
        <div class="card-header bg-white p-0">
            <div class="d-flex">
                <input type="text" class="form-control border-0" id="variant_name[0]" name="variant_name[0]" placeholder="Variant name">
                <button type="button" class="btn btn-light border btn-sm my-1 mr-2 pb-0 shadow-sm">
					<i data-feather="trash" width="16" height="16"></i>
				</button>
            </div>
		</div> <!-- card-header -->

        <div class="card-body p-0">
			<!-- Variant start -->
			<div class="variant-option">
				<div class="variant-option__header p-3">
					<div class="form-row">
						<div class="col-7">
							<div class="form-group m-0">
								<label for="option_name[0]" class="text-muted my-2 d-block"><strong>Option name</strong></label>
								<input type="text" class="form-control" id="option_name[0]" name="option_name[0]" placeholder="Option name">
							</div>
						</div>
						<div class="col-3">
							<div class="form-group m-0">
								<label for="add_amount[0]" class="text-muted my-2 d-block"><strong>Additional amount</strong></label>
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">$</span></div>
									<input type="text" name="add_amount[0]" id="add_amount[0]" class="form-control" placeholder="0.0">
								</div>
							</div>
						</div>
						<div class="col d-flex align-items-center">
							<div class="mt-4">
								<button type="button" class="btn btn-light btn-sm border shadow-sm pb-0"><i data-feather="edit-3" width="20" height="20"></i></button>
								<button type="button" class="btn btn-light btn-sm border shadow-sm pb-0 ml-2"><i data-feather="trash" width="20" height="20"></i></button>
							</div>
						</div>
					</div>

				</div>
				<div class="variant-option-body bg-light p-3">
					<div class="form-group">
						<label for="option_description[0]" class="text-muted my-2 d-block"><strong>Description</strong></label>
						<textarea class="form-control" id="option_description[0]" rows="3"></textarea>
					</div>
					<div class="form-group">
						<label for="option_description[0]" class="text-muted my-2 d-block"><strong>Files</strong></label>
						<div class="border rounded text-center p-4">
							<i data-feather="package" width="42" height="42"></i>
							<h3 class="text-muted">Associate files with this variant</h3>
							<button class="btn btn-light border shadow-sm">Select files</button>
						</div>

					</div>
					<!-- Files selection -->
					<ul class="list-group">
						<li class="list-group-item m-0 d-flex justify-content-between">
							<div class="custom-checkbox custom-checkbox-round">
								<input type="checkbox" class="custom-control-input" id="customCheck1" checked>
								<label class="custom-control-label" for="customCheck1">Cras justo odio</label>
							</div>
						</li>
						<li class="list-group-item m-0">
							<div class="custom-checkbox custom-checkbox-round">
								<input type="checkbox" class="custom-control-input" id="customCheck2">
								<label class="custom-control-label" for="customCheck2">Porta ac consectetur ac</label>
							</div>
						</li>
						<li class="list-group-item m-0">
							<div class="custom-checkbox custom-checkbox-round">
								<input type="checkbox" class="custom-control-input" id="customCheck3">
								<label class="custom-control-label" for="customCheck3">Porta ac consectetur ac</label>
							</div>
						</li>
					</ul>

				</div>
			</div>
			<!-- Variant end -->
		</div> <!-- card body -->

		<div class="card-footer bg-white p3">
			<button class="btn btn-secondary add-variant">Add option</button>
		</div>
    </div> <!-- card -->

</div>
