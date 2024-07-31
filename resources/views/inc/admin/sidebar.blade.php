<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
      <!-- Logo Header -->
      <div class="logo-header mt-5" data-background-color="dark">
        <a href="/admin/dashboard" class="logo">
          <img
            src="{{asset('build/assets/img/007.png')}}"
            alt="navbar brand"
            class="navbar-brand"
            height="150"
          />
        </a>
        <div class="nav-toggle">
          <button class="btn btn-toggle toggle-sidebar">
            <i class="gg-menu-right"></i>
          </button>
          <button class="btn btn-toggle sidenav-toggler">
            <i class="gg-menu-left"></i>
          </button>
        </div>
        <button class="topbar-toggler more">
          <i class="gg-more-vertical-alt"></i>
        </button>
      </div>
      <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper mt-5 scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-item active">
            <a href="/admin/articles">
              <i class="fas fa-tags"></i>
              <p>Articles</p>
            </a>
          </li>
          <li class="nav-item active">
            <a href="/admin/users" >
                <i class="fas fa-solid fa-users"></i>
              <p>Utilisateurs</p>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
          <li class="nav-item active">
            <a href="/admin/vendeurs">
                <i class="fas fa-address-card"></i>
              <p>Vendeurs</p>
            </a>
          </li>
          <li class="nav-item active">
            <a
              {{-- data-bs-toggle="collapse" --}}
              href="/admin/clients"
              class="collapsed"
              aria-expanded="false"
            >
            <i class="fas fa-user-tie"></i>
              <p>Clients</p>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
          <li class="nav-item active">
            <a
              data-bs-toggle="collapse"
              href="#dashboard"
              class="collapsed"
              aria-expanded="false"
            >
            <i class="fas fa-dolly-flatbed"></i>
              <p>Commandes</p>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
