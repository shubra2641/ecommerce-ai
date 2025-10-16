
<!-- Start Shop Newsletter  -->
<section class="shop-newsletter section">
    <div class="container">
        <div class="inner-top">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-12">
                    <!-- Start Newsletter Inner -->
                    <div class="inner">
                        <h4>{{ __('frontend.newsletter.title') }}</h4>
                        <p> {{ __('frontend.newsletter.subscribe_text', ['percent' => '10%']) }}</p>
                        <form action="{{route('subscribe')}}" method="post" class="newsletter-inner">
                            @csrf
                            <button class="btn" type="submit">{{ __('frontend.newsletter.subscribe') }}</button>
                            <input name="email" placeholder="{{ __('frontend.newsletter.placeholder') }}" required="" type="email">
                        </form>
                    </div>
                    <!-- End Newsletter Inner -->
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Shop Newsletter -->