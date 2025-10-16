@extends('frontend.layouts.master')

@section('title', ($settings->site_name ?? config('app.name')) . ' || ' . __('frontend.home.trending_item'))

@section('main-content')
	<!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{ route('home') }}">{{ __('frontend.menu.home') }}<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="blog-single.html">{{ __('frontend.home.trending_item') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Product Style -->
    <form action="{{route('shop.filter')}}" method="POST">
        @csrf
        <section class="product-area shop-sidebar shop section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-12">
                        <div class="shop-sidebar">
                                <!-- Single Widget -->
                                <div class="single-widget category">
                                    <h3 class="title">{{ __('frontend.menu.products') }}</h3>
                                    <ul class="categor-list">
										@if($menu)
										<li>
											@foreach($menu as $cat_info)
													@if($cat_info->child_cat && $cat_info->child_cat->count()>0)
														<li><a href="{{route('product-cat',$cat_info->slug)}}">{{$cat_info->title}}</a>
															<ul>
																@foreach($cat_info->child_cat as $sub_menu)
																	<li><a href="{{route('product-sub-cat',[$cat_info->slug,$sub_menu->slug])}}">{{$sub_menu->title}}</a></li>
																@endforeach
															</ul>
														</li>
													@else
														<li><a href="{{route('product-cat',$cat_info->slug)}}">{{$cat_info->title}}</a></li>
													@endif
											@endforeach
										</li>
										@endif
                                        {{-- @foreach(Helper::productCategoryList('products') as $cat)
                                            @if($cat->is_parent==1)
												<li><a href="{{route('product-cat',$cat->slug)}}">{{$cat->title}}</a></li>
											@endif
                                        @endforeach --}}
                                    </ul>
                                </div>
                                <!--/ End Single Widget -->
                                <!-- Shop By Price -->
                                    <div class="single-widget range">
                                        <h3 class="title">{{ __('frontend.cart.title') }} {{ __('frontend.buttons.apply') }}</h3>
                                        <div class="price-filter">
                                            <div class="price-filter-inner">
                                                <div id="slider-range" data-min="0" data-max="{{$max}}"></div>
                                                <div class="product_filter">
                                                <button type="submit" class="filter_button">Filter</button>
                                                <div class="label-input">
                                                    <span>Range:</span>
                                                    <input type="text" id="amount" readonly/>
                                                    <input type="hidden" name="price_range" id="price_range" value="{{ request()->query('price') ?? '' }}"/>
                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <!--/ End Shop By Price -->
                                <!-- Single Widget -->
                                <div class="single-widget recent-post">
                                    <h3 class="title">{{ __('frontend.home.from_our_blog') }}</h3>
                                    {{-- {{dd($recent_products)}} --}}
                                    @if(!empty($recent_products))
                                    @foreach($recent_products as $product)
                                        <!-- Single Post -->
                                        <div class="single-post first">
                        <div class="image">
                            <img src="{{ $product->first_photo ?? '' }}" alt="{{ $product->first_photo ?? '' }}">
                                                </div>
                                            <div class="content">
                                                <h5><a href="{{route('product-detail',$product->slug)}}">{{$product->title}}</a></h5>
                                                <p class="price"><del class="text-muted">${{number_format($product->price,2)}}</del>   ${{number_format($product->after_discount,2)}}  </p>

                                            </div>
                                        </div>
                                        <!-- End Single Post -->
                                    @endforeach
                                    @endif
                                </div>
                                <!--/ End Single Widget -->
                                <!-- Single Widget -->
                                <div class="single-widget category">
                                    <h3 class="title">{{ __('frontend.labels.new') }}</h3>
                                    <ul class="categor-list">
                                        @if(!empty($brands))
                                        @foreach($brands as $brand)
                                            <li><a href="{{route('product-brand',$brand->slug)}}">{{$brand->title}}</a></li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <!--/ End Single Widget -->
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-8 col-12">
                        <div class="row">
                            <div class="col-12">
                                <!-- Shop Top -->
                                <div class="shop-top">
                                    <div class="shop-shorter">
                                        <div class="single-shorter">
                                            <label>{{ __('frontend.buttons.shop_now') }} :</label>
                                            <select class="show" name="show" onchange="this.form.submit();">
                                                <option value="">Default</option>
                                                <option value="9" @if(request()->query('show') == '9') selected @endif>09</option>
                                                <option value="15" @if(request()->query('show') == '15') selected @endif>15</option>
                                                <option value="21" @if(request()->query('show') == '21') selected @endif>21</option>
                                                <option value="30" @if(request()->query('show') == '30') selected @endif>30</option>
                                            </select>
                                        </div>
                                        <div class="single-shorter">
                                            <label>{{ __('frontend.buttons.discover_now') }} :</label>
                                            <select class='sortBy' name='sortBy' onchange="this.form.submit();">
                                                <option value="">Default</option>
                                                <option value="title" @if(request()->query('sortBy') == 'title') selected @endif>Name</option>
                                                <option value="price" @if(request()->query('sortBy') == 'price') selected @endif>Price</option>
                                                <option value="category" @if(request()->query('sortBy') == 'category') selected @endif>Category</option>
                                                <option value="brand" @if(request()->query('sortBy') == 'brand') selected @endif>Brand</option>
                                            </select>
                                        </div>
                                    </div>
                                    <ul class="view-mode">
                                        <li class="active"><a href="javascript:void(0)"><i class="fa fa-th-large"></i></a></li>
                                        <li><a href="{{route('product-lists')}}"><i class="fa fa-th-list"></i></a></li>
                                    </ul>
                                </div>
                                <!--/ End Shop Top -->
                            </div>
                        </div>
                        <div class="row">
                            {{-- {{$products}} --}}
                            @if($products && (is_array($products) || is_countable($products)) && count($products)>0)
                                @foreach($products as $product)
                                    <div class="col-lg-4 col-md-6 col-12">
                                        <div class="single-product">
                                            <div class="product-img">
                                                <a href="{{route('product-detail',$product->slug)}}">
                                                    <img class="default-img" src="{{ $product->first_photo ?? '' }}" alt="{{ $product->first_photo ?? '' }}">
                                                    <img class="hover-img" src="{{ $product->first_photo ?? '' }}" alt="{{ $product->first_photo ?? '' }}">
                                                    @if($product->discount)
                                                                <span class="price-dec">{{$product->discount}} % Off</span>
                                                    @endif
                                                </a>
                                                <div class="button-head">
                                                    <div class="product-action">
                                                        <a data-toggle="modal" data-target="#{{$product->id}}" title="Quick View" href="#"><i class=" ti-eye"></i><span>{{ __('frontend.product.quick_shop') }}</span></a>
                                                        <a title="Wishlist" href="{{route('add-to-wishlist',$product->slug)}}" class="wishlist" data-id="{{$product->id}}"><i class=" ti-heart "></i><span>{{ __('frontend.product.add_to_wishlist') }}</span></a>
                                                        <a title="Compare" href="{{route('compare.add',$product->slug)}}" class="compare" data-id="{{$product->id}}"><i class="ti-bar-chart-alt"></i><span>{{ __('frontend.product.add_to_compare') }}</span></a>
                                                    </div>
                                                    <div class="product-action-2">
                                                        <a title="Add to cart" href="{{route('add-to-cart',$product->slug)}}">{{ __('frontend.product.add_to_cart') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="product-content">
                                                <h3><a href="{{route('product-detail',$product->slug)}}">{{$product->title}}</a></h3>
                                                <span>${{number_format($product->after_discount,2)}}</span>
                                                <del style="padding-left:4%;">${{number_format($product->price,2)}}</del>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                @else
                    <h4 class="text-warning" style="margin:100px auto;">{{ __('frontend.cart.empty') }}</h4>
                            @endif



                        </div>
                        <div class="row">
                            <div class="col-md-12 justify-content-center d-flex">
                                @if(method_exists($products, 'links'))
                                    {{$products->appends(request()->query())->links()}}
                                @endif
                            </div>
                          </div>

                    </div>
                </div>
            </div>
        </section>
    </form>

    <!--/ End Product Style 1  -->



    <!-- Modal -->
    @if($products && (is_array($products) || is_countable($products)))
        @foreach($products as $key=>$product)
            @if(is_object($product) && isset($product->id))
            <div class="modal fade" id="{{$product->id}}" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span class="ti-close" aria-hidden="true"></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row no-gutters">
                                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                        <!-- Product Slider -->
                                            <div class="product-gallery">
                                                <div class="quickview-slider-active">
                                                    @if(!empty($product->photo_array) && is_array($product->photo_array))
                                                        @foreach($product->photo_array as $data)
                                                            <div class="single-slider">
                                                                <img src="{{$data}}" alt="{{$data}}">
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        <!-- End Product slider -->
                                    </div>
                                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                        <div class="quickview-content">
                                            <h2>{{$product->title}}</h2>
                                            <div class="quickview-ratting-review">
                                                <div class="quickview-ratting-wrap">
                                                    <div class="quickview-ratting">
                                                        {{-- <i class="yellow fa fa-star"></i>
                                                        <i class="yellow fa fa-star"></i>
                                                        <i class="yellow fa fa-star"></i>
                                                        <i class="yellow fa fa-star"></i>
                                                        <i class="fa fa-star"></i> --}}
                                                        @for($i=1; $i<=5; $i++)
                                                            @if(( $product->rate ?? 0 ) >= $i)
                                                                <i class="yellow fa fa-star"></i>
                                                            @else
                                                                <i class="fa fa-star"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <a href="#"> ({{ $product->rate_count ?? 0 }} customer review)</a>
                                                </div>
                                                <div class="quickview-stock">
                                                    @if($product->stock >0)
                                                    <span><i class="fa fa-check-circle-o"></i> {{$product->stock}} in stock</span>
                                                    @else
                                                    <span><i class="fa fa-times-circle-o text-danger"></i> {{$product->stock}} out stock</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <h3><small><del class="text-muted">${{number_format($product->price,2)}}</del></small>    ${{number_format($product->after_discount,2)}}  </h3>
                                            <div class="quickview-peragraph">
                                                <p>{!! safe_html(html_entity_decode($product->summary)) !!}</p>
                                            </div>
                                            @if($product->size)
                                                <div class="size">
                                                    <h4>Size</h4>
                                                    <ul>
                                                        @if(!empty($product->sizes_array) && is_array($product->sizes_array))
                                                            @foreach($product->sizes_array as $size)
                                                            <li><a href="#" class="one">{{$size}}</a></li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            <div class="size">
                                                <div class="row">
                                                    <div class="col-lg-6 col-12">
                                                        <h5 class="title">Size</h5>
                                                        <select>
                                                                @if(!empty($product->sizes_array) && is_array($product->sizes_array))
                                                                    @foreach($product->sizes_array as $size)
                                                                        <option>{{$size}}</option>
                                                                    @endforeach
                                                                @endif
                                                        </select>
                                                    </div>
                                                    {{-- <div class="col-lg-6 col-12">
                                                        <h5 class="title">Color</h5>
                                                        <select>
                                                            <option selected="selected">orange</option>
                                                            <option>purple</option>
                                                            <option>black</option>
                                                            <option>pink</option>
                                                        </select>
                                                    </div> --}}
                                                </div>
                                            </div>
                                            <form action="{{route('single-add-to-cart')}}" method="POST">
                                                @csrf
                                                <div class="quantity">
                                                    <!-- Input Order -->
                                                    <div class="input-group">
                                                        <div class="button minus">
                                                            <button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
                                                                <i class="ti-minus"></i>
                                                            </button>
                                                        </div>
                                                        <input type="hidden" name="slug" value="{{$product->slug}}">
                                                        <input type="text" name="quant[1]" class="input-number"  data-min="1" data-max="1000" value="1">
                                                        <div class="button plus">
                                                            <button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
                                                                <i class="ti-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!--/ End Input Order -->
                                                </div>
                                                <div class="add-to-cart">
                                                    <button type="submit" class="btn">{{ __('frontend.product.add_to_cart') }}</button>
                                                    <a href="{{route('add-to-wishlist',$product->slug)}}" class="btn min"><i class="ti-heart"></i></a>
                                                    <a href="{{route('compare.add',$product->slug)}}" class="btn min"><i class="ti-bar-chart-alt"></i></a>
                                                </div>
                                            </form>
                                            <div class="default-social">
                                            <!-- ShareThis BEGIN --><div class="sharethis-inline-share-buttons"></div><!-- ShareThis END -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            @endif
        @endforeach
    @endif
    <!-- Modal end -->

@endsection
