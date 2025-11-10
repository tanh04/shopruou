@extends('welcome')

@section('content')

	<section id="cart_items" class="py-5">
		<div class="container">
			<div class="card shadow-lg border-0 rounded-4">
				<div class="card-body text-center py-5">
					<div class="rounded-circle bg-success d-inline-flex justify-content-center align-items-center mb-4"
						style="width:80px; height:80px;">
						<i class="bi bi-check2 text-white" style="font-size:2rem;"></i>
					</div>
					<h2 class="fw-bold text-dark mb-3">
						Cảm ơn bạn đã đặt hàng!
					</h2>
					<p class="text-muted fs-5 mb-4">
						Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng.
					</p>

					<div class="d-flex justify-content-center gap-3 mt-4">
						<a href="{{ url('/') }}" class="btn btn-outline-primary px-4 py-2 rounded-pill" style="margin-top: 15px; font-size: 20px;">
							<i class="bi bi-house-door"></i> Về trang chủ
						</a>
						<a href="{{ route('order.history') }}" class="btn btn-primary px-4 py-2 rounded-pill">
							<i class="bi bi-receipt"></i> Lịch sử đơn hàng
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>


    
@endsection