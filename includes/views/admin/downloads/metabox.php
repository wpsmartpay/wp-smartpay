<div class="smartpay" style="margin: -6px -12px -12px -12px;">
	<div class="d-flex">
		<div class="col-3 bg-light border-right">
			<div class="py-3">
				<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
					<a class="nav-link text-decoration-none d-flex align-items-center active" id="smartpay-files-tab" data-toggle="pill" href="#smartpay-files" role="tab" aria-controls="smartpay-files" aria-selected="true">
						<i data-feather="hard-drive" width="14" height="14"></i> <span class="ml-2">Files</span>
					</a>
					<a class="nav-link text-decoration-none d-flex align-items-center" id="smartpay-pricing-tab" data-toggle="pill" href="#smartpay-pricing" role="tab" aria-controls="smartpay-pricing" aria-selected="false">
						<i data-feather="dollar-sign" width="14" height="14"></i> <span class="ml-2">Pricing</span>
					</a>
				</div>
			</div>
		</div>
		<div class="col-9">
			<div class="tab-content py-3" id="smartpay-tabContent">
				<div class="tab-pane fade show active" id="smartpay-files" role="tabpanel" aria-labelledby="smartpay-files-tab">
					<?php include "metabox/files.php"?>
				</div>
				<div class="tab-pane fade" id="smartpay-pricing" role="tabpanel" aria-labelledby="smartpay-pricing-tab">
					<?php include "metabox/pricing.php"?>
				</div>
			</div>
		</div>
	</div>
</div>
