<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('admin')}}">
      <div class="sidebar-brand-icon rotate-n-15">
        <i class="fas fa-laugh-wink"></i>
      </div>
      <div class="sidebar-brand-text mx-3">{{ trans('app.admin') }}</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
      <a class="nav-link" href="{{route('admin')}}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>{{ trans('app.dashboard') }}</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ trans('app.banner') }}
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <!-- Nav Item - Charts -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('file-manager')}}">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>{{ trans('app.media_manager') }}</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-image"></i>
        <span>{{ trans('app.banners') }}</span>
      </a>
      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">{{ trans('app.banner_options') }}:</h6>
          <a class="collapse-item" href="{{route('banner.index')}}">{{ trans('app.banners') }}</a>
          <a class="collapse-item" href="{{route('banner.create')}}">{{ trans('app.add_banners') }}</a>
        </div>
      </div>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
        <!-- Heading -->
        <div class="sidebar-heading">
            {{ trans('app.shop') }}
        </div>

    <!-- Categories -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#categoryCollapse" aria-expanded="true" aria-controls="categoryCollapse">
          <i class="fas fa-sitemap"></i>
          <span>{{ trans('app.category') }}</span>
        </a>
        <div id="categoryCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.category') }} {{ trans('app.settings') }}:</h6>
            <a class="collapse-item" href="{{route('category.index')}}">{{ trans('app.category') }}</a>
            <a class="collapse-item" href="{{route('category.create')}}">{{ trans('app.create') }} {{ trans('app.category') }}</a>
          </div>
        </div>
    </li>

     {{-- Brands --}}
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#brandCollapse" aria-expanded="true" aria-controls="brandCollapse">
          <i class="fas fa-table"></i>
          <span>{{ trans('app.brands') }}</span>
        </a>
        <div id="brandCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.brand_options') }}:</h6>
            <a class="collapse-item" href="{{route('brand.index')}}">{{ trans('app.brands') }}</a>
            <a class="collapse-item" href="{{route('brand.create')}}">{{ trans('app.add_brand') }}</a>
          </div>
        </div>
    </li>



    {{-- Products --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#productCollapse" aria-expanded="true" aria-controls="productCollapse">
          <i class="fas fa-cubes"></i>
          <span>{{ trans('app.products') }}</span>
        </a>
        <div id="productCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.product_options') }}:</h6>
            <a class="collapse-item" href="{{route('product.index')}}">{{ trans('app.products') }}</a>
            <a class="collapse-item" href="{{route('product.create')}}">{{ trans('app.add_product') }}</a>
          </div>
        </div>
    </li>

  
    {{-- Shipping --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#shippingCollapse" aria-expanded="true" aria-controls="shippingCollapse">
          <i class="fas fa-truck"></i>
          <span>{{ trans('app.shipping') }}</span>
        </a>
        <div id="shippingCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.shipping_options') }}:</h6>
            <a class="collapse-item" href="{{route('shipping.index')}}">{{ trans('app.shipping') }}</a>
            <a class="collapse-item" href="{{route('shipping.create')}}">{{ trans('app.add_shipping') }}</a>
          </div>
        </div>
    </li>

    <!--Orders -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('order.index')}}">
            <i class="fas fa-hammer fa-chart-area"></i>
            <span>{{ trans('app.orders') }}</span>
        </a>
    </li>

    <!-- Reviews -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('review.index')}}">
            <i class="fas fa-comments"></i>
            <span>{{ trans('app.reviews') }}</span></a>
    </li>
    

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
      {{ trans('app.posts') }}
    </div>

    <!-- Posts -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#postCollapse" aria-expanded="true" aria-controls="postCollapse">
        <i class="fas fa-fw fa-folder"></i>
        <span>{{ trans('app.posts') }}</span>
      </a>
      <div id="postCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">{{ trans('app.post_options') }}:</h6>
          <a class="collapse-item" href="{{route('post.index')}}">{{ trans('app.posts') }}</a>
          <a class="collapse-item" href="{{route('post.create')}}">{{ trans('app.add_post') }}</a>
        </div>
      </div>
    </li>

     <!-- Category -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#postCategoryCollapse" aria-expanded="true" aria-controls="postCategoryCollapse">
          <i class="fas fa-sitemap fa-folder"></i>
          <span>{{ trans('app.category') }}</span>
        </a>
        <div id="postCategoryCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.category') }} {{ trans('app.settings') }}:</h6>
            <a class="collapse-item" href="{{route('post-category.index')}}">{{ trans('app.category') }}</a>
            <a class="collapse-item" href="{{route('post-category.create')}}">{{ trans('app.create') }} {{ trans('app.category') }}</a>
          </div>
        </div>
      </li>

      <!-- Tags -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#tagCollapse" aria-expanded="true" aria-controls="tagCollapse">
            <i class="fas fa-tags fa-folder"></i>
            <span>{{ trans('app.tags') }}</span>
        </a>
        <div id="tagCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.tag_options') }}:</h6>
            <a class="collapse-item" href="{{route('post-tag.index')}}">{{ trans('app.tags') }}</a>
            <a class="collapse-item" href="{{route('post-tag.create')}}">{{ trans('app.add_tag') }}</a>
            </div>
        </div>
    </li>

      <!-- Comments -->
      <li class="nav-item">
        <a class="nav-link" href="{{route('comment.index')}}">
            <i class="fas fa-comments fa-chart-area"></i>
            <span>{{ trans('app.comments') }}</span>
        </a>
      </li>


    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
     <!-- Heading -->
    <div class="sidebar-heading">
        {{ trans('app.general_settings') }}
    </div>
    <li class="nav-item">
      <a class="nav-link" href="{{route('coupon.index')}}">
          <i class="fas fa-table"></i>
          <span>{{ trans('app.coupon') }}</span></a>
    </li>
     <!-- Users -->
     <li class="nav-item">
        <a class="nav-link" href="{{route('users.index')}}">
            <i class="fas fa-users"></i>
            <span>{{ trans('app.users') }}</span></a>
    </li>
     <!-- Language Management -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#languageCollapse" aria-expanded="true" aria-controls="languageCollapse">
          <i class="fas fa-globe"></i>
          <span>{{ trans('app.languages') }}</span>
        </a>
        <div id="languageCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">{{ trans('app.language_options') }}:</h6>
            <a class="collapse-item" href="{{route('language.index')}}">{{ trans('app.all_languages') }}</a>
            <a class="collapse-item" href="{{route('language.create')}}">{{ trans('app.add_language') }}</a>
          </div>
        </div>
    </li>

     <!-- General settings -->
     <li class="nav-item">
        <a class="nav-link" href="{{route('settings')}}">
            <i class="fas fa-cog"></i>
            <span>{{ trans('app.settings') }}</span></a>
    </li>

  <!-- Payment Gateways -->
  <li class="nav-item">
     <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#paymentCollapse" aria-expanded="true" aria-controls="paymentCollapse">
       <i class="fas fa-credit-card"></i>
       <span>{{ trans('app.payment_gateways') }}</span>
     </a>
     <div id="paymentCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
       <div class="bg-white py-2 collapse-inner rounded">
         <h6 class="collapse-header">Payment Management:</h6>
         <a class="collapse-item" href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a>
       </div>
     </div>
  </li>

  <!-- Newsletter Management -->
  <li class="nav-item">
     <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#newsletterCollapse" aria-expanded="true" aria-controls="newsletterCollapse">
       <i class="fas fa-newspaper"></i>
       <span>{{ __('newsletter.newsletter_management') }}</span>
     </a>
     <div id="newsletterCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
       <div class="bg-white py-2 collapse-inner rounded">
         <h6 class="collapse-header">{{ __('newsletter.newsletter_options') }}:</h6>
         <a class="collapse-item" href="{{ route('admin.newsletter.index') }}">{{ __('newsletter.subscribers') }}</a>
         <a class="collapse-item" href="{{ route('admin.newsletter.create') }}">{{ __('newsletter.send_newsletter') }}</a>
       </div>
     </div>
  </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>